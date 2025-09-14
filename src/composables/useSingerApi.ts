import { apiAuth } from '@/utils/apiClient'
import type { Singer, SingerFormInput } from '@/types'

export function useSingerApi() {
    const handleRequest = async <T>(fn: () => Promise<T>): Promise<T> => {
        try {
            return await fn()
        } catch (error: any) {
            console.error('Singer API error:', error)

            const status = error?.response?.status
            const data = error?.response?.data

            if (status === 422 && data?.validation) {
                // バリデーションエラー（422）
                throw {
                    type: 'validation',
                    errors: data.validation, // { field: ['メッセージ', ...], ... }
                }
            }

            const message = data?.message ?? '通信エラーが発生しました'

            throw {
                type: 'error',
                message,
            }
        }
    }

    const fetchSingers = async (): Promise<Singer[]> =>
        handleRequest(async () => {
            const res = await apiAuth.get<{ data: Singer[] }>('/singer/list')
            return res.data.data
        })

    const createSinger = async (form: SingerFormInput): Promise<Singer> =>
        handleRequest(async () => {
            const res = await apiAuth.post<{ data: Singer }>(
                '/singer/create',
                form,
            )
            return res.data.data
        })

    const editSinger = async (form: SingerFormInput): Promise<Singer> =>
        handleRequest(async () => {
            const res = await apiAuth.post<{ data: Singer }>(
                '/singer/edit',
                form,
            )
            return res.data.data
        })

    const deleteSinger = async (id: number): Promise<void> =>
        handleRequest(async () => {
            await apiAuth.post('/singer/delete', { id })
        })

    return {
        fetchSingers,
        createSinger,
        editSinger,
        deleteSinger,
    }
}
