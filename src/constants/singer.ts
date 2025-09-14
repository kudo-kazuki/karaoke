export const GENDER = {
    1: '男性',
    2: '女性',
    3: '不明',
} as const

export const formatGender = (_row: any, _column: any, value: number | null) => {
    return GENDER[(value ?? 3) as keyof typeof GENDER] ?? '不明'
}

export const SOLO_OR_GROUP = {
    0: 'ソロ',
    1: 'グループ',
} as const

export const formatGroup = (_row: any, _column: any, value: boolean) => {
    return SOLO_OR_GROUP[Number(value) as 0 | 1] ?? 'ソロ'
}

// <el-select> 用オプション配列
export const GENDER_OPTIONS = Object.entries(GENDER).map(([value, label]) => ({
    value: Number(value),
    label,
}))

export const SOLO_OR_GROUP_OPTIONS = Object.entries(SOLO_OR_GROUP).map(
    ([value, label]) => ({
        value: Number(value),
        label,
    }),
)
