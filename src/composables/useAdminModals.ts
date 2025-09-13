import { ref, computed } from 'vue'
import dayjs from 'dayjs'
import { apiAuth } from '@/utils/apiClient'
import { useAdminAuthStore } from '@/stores/admin_auth'
import type { Admin } from '@/types'
import { useAdminList } from '@/composables/useAdminList'

export function useAdminModals() {
    const adminAuthStore = useAdminAuthStore()
    const { adminList, fetchAdminList } = useAdminList()

    const isLoading = ref(false)
    const isLoadingText = ref('')
    const selectedAdminData = ref<Admin | null>(null)

    const formatDate = (value: string) =>
        dayjs(value).format('YYYY-MM-DD HH:mm:ss')

    // ==================== 編集 ==================== //
    const editedAdminName = ref('')
    const editedAdminPassword = ref('')
    const editedAdminLevel = ref(0)
    const editedAdminRemarks = ref('')
    const isOpenEditAdminModal = ref(false)

    const openEditAdmin = (admin: Admin) => {
        selectedAdminData.value = admin
        editedAdminName.value = admin.name
        editedAdminPassword.value = ''
        editedAdminLevel.value = admin.level
        editedAdminRemarks.value = admin.remarks ?? ''
        isOpenEditAdminModal.value = true
    }

    const closeEditAdmin = () => {
        isOpenEditAdminModal.value = false
        selectedAdminData.value = null
    }

    const saveEditAdmin = async () => {
        if (
            isLoading.value ||
            !editedAdminName.value ||
            typeof editedAdminLevel.value !== 'number' ||
            selectedAdminData.value === null
        )
            return false

        isLoading.value = true
        isLoadingText.value = '保存中'

        try {
            await apiAuth.post('/admin/edit', {
                id: selectedAdminData.value.id,
                name: editedAdminName.value,
                level: editedAdminLevel.value,
                remarks: editedAdminRemarks.value,
                password: editedAdminPassword.value || '',
            })

            isLoadingText.value = '成功'
            setTimeout(() => {
                isLoading.value = false
                closeEditAdmin()
            }, 1000)

            if (adminAuthStore.level === 0) {
                await fetchAdminList()
            }
        } catch (error) {
            console.error('編集に失敗しました', error)
            isLoadingText.value = '失敗'
            isLoading.value = false
        }
    }

    // ==================== 削除 ==================== //
    const isOpenDeleteAdminModal = ref(false)

    const openDeleteAdmin = (admin: Admin) => {
        selectedAdminData.value = admin
        isOpenDeleteAdminModal.value = true
    }

    const closeDeleteAdmin = () => {
        isOpenDeleteAdminModal.value = false
        selectedAdminData.value = null
    }

    const deleteAdmin = async () => {
        if (isLoading.value || selectedAdminData.value === null) return false

        isLoading.value = true
        isLoadingText.value = '削除中'

        try {
            await apiAuth.post('/admin/delete', {
                id: selectedAdminData.value.id,
            })

            isLoadingText.value = '成功'
            setTimeout(() => {
                isLoading.value = false
                closeDeleteAdmin()
            }, 1000)

            if (adminAuthStore.level === 0) {
                await fetchAdminList()
            }
        } catch (error) {
            console.error('削除に失敗しました', error)
            isLoadingText.value = '失敗'
            isLoading.value = false
        }
    }

    // ==================== 作成 ==================== //
    const createAdminName = ref('')
    const createAdminPassword = ref('')
    const createAdminLevel = ref(0)
    const createAdminRemarks = ref('')
    const isOpenCreateAdminModal = ref(false)

    const openCreateAdmin = () => {
        isOpenCreateAdminModal.value = true
    }

    const closeCreateAdmin = () => {
        isOpenCreateAdminModal.value = false
    }

    const isAdminCreateOk = computed(() => {
        return (
            createAdminName.value &&
            createAdminPassword.value &&
            typeof createAdminLevel.value === 'number'
        )
    })

    const saveCreateAdmin = async () => {
        if (isLoading.value || !isAdminCreateOk.value) return false

        isLoading.value = true
        isLoadingText.value = '作成中'

        try {
            await apiAuth.post('/admin/create', {
                name: createAdminName.value,
                password: createAdminPassword.value,
                level: createAdminLevel.value,
                remarks: createAdminRemarks.value,
            })

            isLoadingText.value = '成功'
            setTimeout(() => {
                isLoading.value = false
                closeCreateAdmin()
            }, 1000)

            if (adminAuthStore.level === 0) {
                await fetchAdminList()
            }
        } catch (error) {
            console.error('作成に失敗しました', error)
            isLoadingText.value = '失敗'
            isLoading.value = false
        }
    }

    return {
        adminList,
        fetchAdminList,
        isLoading,
        isLoadingText,
        selectedAdminData,
        formatDate,

        // 編集
        editedAdminName,
        editedAdminPassword,
        editedAdminLevel,
        editedAdminRemarks,
        isOpenEditAdminModal,
        openEditAdmin,
        closeEditAdmin,
        saveEditAdmin,

        // 削除
        isOpenDeleteAdminModal,
        openDeleteAdmin,
        closeDeleteAdmin,
        deleteAdmin,

        // 作成
        createAdminName,
        createAdminPassword,
        createAdminLevel,
        createAdminRemarks,
        isOpenCreateAdminModal,
        openCreateAdmin,
        closeCreateAdmin,
        isAdminCreateOk,
        saveCreateAdmin,
    }
}
