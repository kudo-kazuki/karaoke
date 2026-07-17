import { existsSync } from 'node:fs'
import { readFile } from 'node:fs/promises'
import { resolve } from 'node:path'
import { spawn } from 'node:child_process'
import { createRequire } from 'node:module'

const require = createRequire(import.meta.url)
const { Client, FileType } = require('basic-ftp')

const colors = {
    blue: '\x1b[34m',
    green: '\x1b[32m',
    red: '\x1b[31m',
    yellow: '\x1b[33m',
    reset: '\x1b[0m',
}

const args = process.argv.slice(2)
const projectRoot = process.cwd()
const isDryRun = args.includes('--dry-run')

const getArgValue = (values, name) => {
    const index = values.indexOf(name)
    return index === -1 ? undefined : values[index + 1]
}

const configPath = getArgValue(args, '--config') ?? 'scripts/ftp.deploy.json'
const target = getArgValue(args, '--target') ?? 'web'

const main = async () => {
    if (!['web', 'api'].includes(target)) {
        throw new Error(`Unknown deploy target: ${target}`)
    }

    logStep('START', `FTP deploy started. target=${target}`)

    const config = await readConfig(configPath)
    const remoteRootDir = normalizeRemoteDir(config.remoteDir)
    validateConfig(config, remoteRootDir)

    if (target === 'web') {
        await deployWeb(config, remoteRootDir)
        return
    }

    await deployApi(config, remoteRootDir)
}

const deployWeb = async (config, remoteRootDir) => {
    const localDir = resolve(projectRoot, config.localDir ?? 'dist')

    await runBuild()

    if (!existsSync(localDir)) {
        throw new Error(`Build output was not found: ${localDir}`)
    }

    logStep('OK', `Build output found: ${localDir}`)

    await withFtpClient(config, async (client) => {
        await prepareRemoteDir(client, remoteRootDir)

        logStep('FTP', 'Removing remote index.html and assets only...')
        const removedCount = await removeWebPublishTargets(client)
        logStep('OK', `Remote cleanup completed. Removed ${removedCount} item(s).`)

        logStep('FTP', `Uploading ${localDir} to ${remoteRootDir}...`)
        await client.uploadFromDir(localDir)
        logStep('SUCCESS', 'Web FTP deploy completed.')
    })
}

const deployApi = async (config, remoteRootDir) => {
    const localDir = resolve(projectRoot, config.apiLocalDir ?? 'api')
    const remoteApiDir = normalizeRemoteDir(config.apiRemoteDir ?? `${remoteRootDir}/api`)

    validateApiRemoteDir(remoteRootDir, remoteApiDir)

    if (!existsSync(localDir)) {
        throw new Error(`API directory was not found: ${localDir}`)
    }

    logStep('OK', `API directory found: ${localDir}`)

    await withFtpClient(config, async (client) => {
        await prepareRemoteDir(client, remoteApiDir)

        logStep('FTP', `Cleaning remote API directory contents: ${remoteApiDir}`)
        const removedCount = await removeRemoteContents(client)
        logStep('OK', `Remote API cleanup completed. Removed ${removedCount} item(s).`)

        logStep('FTP', `Uploading ${localDir} to ${remoteApiDir}...`)
        await client.uploadFromDir(localDir)
        logStep('SUCCESS', 'API FTP deploy completed.')
    })
}

const withFtpClient = async (config, callback) => {
    if (isDryRun) {
        logStep('DRY-RUN', 'Skipping FTP connection, remote cleanup, and upload.')
        return
    }

    const client = new Client()
    client.ftp.verbose = Boolean(config.verbose)

    try {
        logStep('FTP', `Connecting to ${config.host}:${config.port ?? 21}...`)
        await client.access({
            host: config.host,
            port: config.port ?? 21,
            user: config.user,
            password: config.password,
            secure: config.secure ?? false,
            secureOptions: config.secureOptions,
        })
        logStep('OK', 'FTP connection established.')

        await callback(client)
    } finally {
        client.close()
    }
}

const prepareRemoteDir = async (client, remoteDir) => {
    logStep('FTP', `Preparing remote directory: ${remoteDir}`)
    await client.ensureDir(remoteDir)
    await client.cd(remoteDir)
    logStep('OK', 'Remote directory is ready.')
}

const readConfig = async (path) => {
    const fullPath = resolve(projectRoot, path)

    if (!existsSync(fullPath)) {
        throw new Error(
            `FTP config was not found: ${fullPath}\n` +
                'Create scripts/ftp.deploy.json from scripts/ftp.deploy.example.json.'
        )
    }

    const content = await readFile(fullPath, 'utf8')
    return JSON.parse(content.replace(/^\uFEFF/u, ''))
}

const validateConfig = (config, remoteRootDir) => {
    const requiredFields = ['host', 'user', 'password', 'remoteDir']
    const missing = requiredFields.filter((field) => !config[field])

    if (missing.length > 0) {
        throw new Error(`Missing FTP config field(s): ${missing.join(', ')}`)
    }

    if (remoteRootDir === '/' || remoteRootDir === '.' || remoteRootDir === '') {
        throw new Error('Refusing to clean an unsafe remoteDir. Use a specific deploy directory.')
    }

}

const validateApiRemoteDir = (remoteRootDir, remoteApiDir) => {
    const expectedApiDir = `${remoteRootDir}/api`

    if (remoteApiDir !== expectedApiDir) {
        throw new Error(`Refusing to clean unexpected API directory: ${remoteApiDir}`)
    }
}

const normalizeRemoteDir = (remoteDir) => {
    return String(remoteDir ?? '')
        .replaceAll('\\', '/')
        .replace(/\/+$/u, '')
}

const runBuild = async () => {
    logStep('BUILD', 'Running npm run build...')
    await runCommandWithFallback(getNpmCommands(), ['run', 'build'])
    logStep('OK', 'Build completed.')
}

const runCommandWithFallback = async (commands, commandArgs) => {
    let lastError

    for (const command of commands) {
        try {
            await runCommand(command, commandArgs)
            return
        } catch (error) {
            lastError = error

            if (error.code !== 'ENOENT' && error.code !== 'EINVAL') {
                throw error
            }

            logStep('WARN', `Failed to start ${command}: ${error.code}. Trying fallback command...`)
        }
    }

    throw lastError
}

const getNpmCommands = () => {
    if (process.platform !== 'win32') {
        return ['npm']
    }

    const commands = ['npm.cmd']
    const programFilesNpm = 'C:\\Program Files\\nodejs\\npm.cmd'

    if (existsSync(programFilesNpm)) {
        commands.push(programFilesNpm)
    }

    return commands
}

const runCommand = (command, commandArgs) => {
    return new Promise((resolvePromise, reject) => {
        const child = spawn(command, commandArgs, {
            cwd: projectRoot,
            stdio: 'inherit',
            shell: process.platform === 'win32',
        })

        child.on('error', reject)
        child.on('exit', (code) => {
            if (code === 0) {
                resolvePromise()
                return
            }

            reject(new Error(`${command} ${commandArgs.join(' ')} failed with exit code ${code}`))
        })
    })
}

const removeWebPublishTargets = async (client) => {
    const entries = await client.list()
    let removedCount = 0

    for (const entry of entries) {
        if (entry.name === 'index.html' && !isRemoteDirectory(entry)) {
            await client.remove(entry.name)
            removedCount += 1
            continue
        }

        if (entry.name === 'assets' && isRemoteDirectory(entry)) {
            await client.removeDir(entry.name)
            removedCount += 1
        }
    }

    return removedCount
}

const removeRemoteContents = async (client) => {
    const entries = await client.list()
    let removedCount = 0

    for (const entry of entries) {
        if (entry.name === '.' || entry.name === '..') {
            continue
        }

        if (isRemoteDirectory(entry)) {
            await client.removeDir(entry.name)
            removedCount += 1
            continue
        }

        await client.remove(entry.name)
        removedCount += 1
    }

    return removedCount
}

const isRemoteDirectory = (entry) => {
    return entry.isDirectory === true || entry.type === FileType.Directory
}

const logStep = (label, message) => {
    const colorByLabel = {
        START: colors.blue,
        BUILD: colors.blue,
        FTP: colors.blue,
        'DRY-RUN': colors.blue,
        OK: colors.green,
        SUCCESS: colors.green,
        WARN: colors.yellow,
        FAILED: colors.red,
    }
    const color = colorByLabel[label]
    const line = color
        ? `${color}[deploy:${label}] ${message}${colors.reset}`
        : `[deploy:${label}] ${message}`

    if (label === 'FAILED') {
        console.error(line)
        return
    }

    console.log(line)
}

main().catch((error) => {
    logStep('FAILED', error.message)
    process.exitCode = 1
})
