import { ref, computed } from 'vue'
import dayjs from 'dayjs'
import { apiAuth } from '@/utils/apiClient'
import type { Singer, SingerFormInput } from '@/types'
import { useSingerApi } from '@/composables/useSingerApi'
import { useSingerList } from '@/composables/useSingerList'
import { formatApiError } from '@/utils/formatApiError'

export function useSingerModals() {
    const { singerList, fetchSingerList } = useSingerList()
    const { createSinger, editSinger, deleteSinger } = useSingerApi()

    const isLoading = ref(false)
    const isLoadingText = ref('')
    const selectedSingerData = ref<Singer | null>(null)

    const modalErrorMessage = ref('')

    const formatDate = (value: string) =>
        dayjs(value).format('YYYY-MM-DD HH:mm:ss')

    // ==================== 編集 ==================== //
    const editSingerForm = ref<SingerFormInput>({
        id: 0,
        name: '',
        gender: null,
        is_group: 0,
        debut_date: '2000-01-01',
        description: '',
    })
    const isOpenEditSingerModal = ref(false)

    const openEditSinger = (singer: Singer) => {
        selectedSingerData.value = singer
        editSingerForm.value.id = singer.id
        editSingerForm.value.name = singer.name
        editSingerForm.value.gender = singer.gender ?? null
        editSingerForm.value.is_group = singer.is_group ?? 0
        editSingerForm.value.debut_date = singer.debut_date ?? '2000-01-01'
        editSingerForm.value.description = singer.description
        isOpenEditSingerModal.value = true
        modalErrorMessage.value = ''
    }

    const closeEditSinger = () => {
        isOpenEditSingerModal.value = false
        selectedSingerData.value = null
    }

    const isSingerEditOk = computed(() => {
        return editSingerForm.value.name
    })

    const saveEditSinger = async () => {
        if (
            isLoading.value ||
            !editSingerForm.value.name ||
            selectedSingerData.value === null
        )
            return false

        isLoading.value = true
        isLoadingText.value = '保存中'

        try {
            const edited = await editSinger(editSingerForm.value)
            console.log('編集成功:', edited)

            // 成功後の処理（モーダルを閉じる、フォームをリセットするなど）
            isLoadingText.value = '成功'
            setTimeout(() => {
                isLoading.value = false
                closeEditSinger()
                editSingerForm.value = {
                    id: 0,
                    name: '',
                    gender: null,
                    is_group: 0,
                    debut_date: '2000-01-01',
                    description: '',
                }
            }, 1000)

            await fetchSingerList()
        } catch (e: any) {
            if (e.type === 'validation') {
                modalErrorMessage.value = e.errors
            } else {
                modalErrorMessage.value = `編集に失敗しました<br>${e.message ?? ''}`
            }
            isLoadingText.value = '失敗'
            isLoading.value = false
        }
    }

    // ==================== 作成 ==================== //
    const createSingerForm = ref<SingerFormInput>({
        name: '',
        gender: null,
        is_group: 0,
        debut_date: '2000-01-01',
        description: '',
    })
    const isOpenCreateSingerModal = ref(false)

    const openCreateSinger = () => {
        isOpenCreateSingerModal.value = true
        modalErrorMessage.value = ''
    }

    const closeCreateSinger = () => {
        isOpenCreateSingerModal.value = false
    }

    const isSingerCreateOk = computed(() => {
        return createSingerForm.value.name
    })

    const saveCreateSinger = async () => {
        if (isLoading.value || !isSingerCreateOk.value) return false

        isLoading.value = true
        isLoadingText.value = '作成中'

        try {
            const created = await createSinger(createSingerForm.value)
            console.log('作成成功:', created)

            // 成功後の処理（モーダルを閉じる、フォームをリセットするなど）
            isLoadingText.value = '成功'
            setTimeout(() => {
                isLoading.value = false
                closeCreateSinger()
                createSingerForm.value = {
                    name: '',
                    gender: null,
                    is_group: 0,
                    debut_date: '',
                    description: '',
                }
            }, 1000)

            await fetchSingerList()
        } catch (e: any) {
            if (e.type === 'validation') {
                modalErrorMessage.value = e.errors
            } else {
                modalErrorMessage.value = `作成に失敗しました<br>${e.message ?? ''}`
            }
            isLoadingText.value = '失敗'
            isLoading.value = false
        }
    }

    // ==================== 削除 ==================== //
    const isOpenDeleteSingerModal = ref(false)

    const openDeleteSinger = (singer: Singer) => {
        selectedSingerData.value = singer
        isOpenDeleteSingerModal.value = true
        modalErrorMessage.value = ''
    }

    const closeDeleteSinger = () => {
        isOpenDeleteSingerModal.value = false
        selectedSingerData.value = null
    }

    const runDeleteSinger = async () => {
        if (isLoading.value || selectedSingerData.value === null) return false

        isLoading.value = true
        isLoadingText.value = '保存中'

        try {
            const edited = await deleteSinger(selectedSingerData.value.id)
            console.log('削除成功:', edited)

            // 成功後の処理（モーダルを閉じる、フォームをリセットするなど）
            isLoadingText.value = '成功'
            setTimeout(() => {
                isLoading.value = false
                closeDeleteSinger()
            }, 1000)

            await fetchSingerList()
        } catch (e: any) {
            if (e.type === 'validation') {
                modalErrorMessage.value = e.errors
            } else {
                modalErrorMessage.value = `削除に失敗しました<br>${e.message ?? ''}`
            }
            isLoadingText.value = '失敗'
            isLoading.value = false
        }
    }

    return {
        singerList,
        fetchSingerList,
        isLoading,
        isLoadingText,
        selectedSingerData,
        formatDate,
        modalErrorMessage,

        // 削除
        isOpenDeleteSingerModal,
        openDeleteSinger,
        closeDeleteSinger,
        runDeleteSinger,

        // 編集
        editSingerForm,
        isOpenEditSingerModal,
        openEditSinger,
        closeEditSinger,
        saveEditSinger,
        isSingerEditOk,

        // 作成
        createSingerForm,
        isOpenCreateSingerModal,
        openCreateSinger,
        closeCreateSinger,
        isSingerCreateOk,
        saveCreateSinger,
    }
}
