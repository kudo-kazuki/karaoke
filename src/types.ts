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
