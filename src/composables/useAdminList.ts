import { ref } from 'vue'
import { apiAuth } from '@/utils/apiClient'
import type { Admin } from '@/types'

export function useAdminList() {
    const adminList = ref<Admin[]>([])

    const fetchAdminList = async () => {
        try {
            const response = await apiAuth.get('/admin/list')
            if (response.data.success) {
                adminList.value = response.data.data
            } else {
                adminList.value = []
            }
        } catch (error) {
            console.error('APIエラー:', error)
            adminList.value = []
        }
    }

    return {
        adminList,
        fetchAdminList,
    }
}
