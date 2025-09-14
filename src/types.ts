export interface AdminJWTPayload {
    exp: number
    name: string
    level: number | null
    sub: number
    role: string
}

export interface Admin {
    id: number
    name: string
    level: number
    remarks?: string
    created_at: string
    updated_at: string
}

export interface AdminAuthState {
    token: string | null
    isAuthenticated: boolean
    name: string | null
    level: number | null
    adminList: Admin[]
    userListLoading?: boolean
    userStatusChangeLoading?: boolean
}

export interface Singer {
    id: number
    name: string
    gender: 1 | 2 | 3 | null // 1:男性, 2:女性, 3:不明, null:グループなど
    is_group: 0 | 1
    debut_date: string // YYYY-MM-DD 形式
    description?: string | null
    created_at: string
    updated_at: string
}

export interface SingerFormInput {
    id?: number // 編集時のみ
    name: string
    gender: 1 | 2 | 3 | null
    is_group: 0 | 1
    debut_date: string // YYYY-MM-DD
    description?: string | null
}
