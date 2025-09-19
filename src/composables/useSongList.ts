import { ref } from 'vue'
import type { Song, SongFormInput } from '@/types'
import { useSongApi } from './useSongApi'

const { fetchSongsBySingerId, createSong, editSong, deleteSong } = useSongApi()

export function useSongList() {
    const songList = ref<Song[]>([])
    const isLoadingSongList = ref(false)
    const isCreatingSong = ref(false)
    const isEditingSong = ref(false)
    const isDeletingSong = ref(false)

    const fetchSongListBySingerId = async (singerId: number) => {
        isLoadingSongList.value = true
        try {
            songList.value = await fetchSongsBySingerId(singerId)
        } finally {
            isLoadingSongList.value = false
        }
    }

    const createSongAndReload = async (
        singerId: number,
        songData: SongFormInput,
    ) => {
        if (isCreatingSong.value)
            return { success: false, message: '処理中です' }

        isCreatingSong.value = true
        try {
            const response = await createSong(songData)
            if (response.success) {
                await fetchSongListBySingerId(singerId)
            }
            return response
        } catch (e: any) {
            return {
                success: false,
                message: e.response?.data?.message ?? '登録に失敗しました',
            }
        } finally {
            isCreatingSong.value = false
        }
    }

    const editSongAndReload = async (
        singerId: number,
        songData: SongFormInput,
    ) => {
        if (isEditingSong.value)
            return { success: false, message: '処理中です' }

        isEditingSong.value = true
        try {
            const response = await editSong(songData)
            if (response.success) {
                await fetchSongListBySingerId(singerId)
            }
            return response
        } catch (e: any) {
            return {
                success: false,
                message: e.response?.data?.message ?? '更新に失敗しました',
            }
        } finally {
            isEditingSong.value = false
        }
    }

    const deleteSongAndReload = async (singerId: number, songId: number) => {
        if (isDeletingSong.value)
            return { success: false, message: '処理中です' }
        isDeletingSong.value = true
        try {
            const response = await deleteSong(songId)
            if (response.success) {
                await fetchSongListBySingerId(singerId)
            }
            return response
        } catch (e: any) {
            return {
                success: false,
                message: e.response?.data?.message ?? '削除に失敗しました',
            }
        } finally {
            isDeletingSong.value = false
        }
    }

    return {
        songList,
        isLoadingSongList,
        isCreatingSong,
        isEditingSong,
        isDeletingSong,
        fetchSongListBySingerId,
        createSongAndReload,
        editSongAndReload,
        deleteSongAndReload,
    }
}
