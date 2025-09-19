import { ref } from 'vue'
import { api, apiAuth } from '@/utils/apiClient'
import type { Song, SongFormInput } from '@/types'

interface ApiResponse<T> {
    success: boolean
    data: T
    message?: string
}

export function useSongApi() {
    const isLoadingSongs = ref(false)

    const fetchSongsBySingerId = async (singerId: number): Promise<Song[]> => {
        if (isLoadingSongs.value) return []

        isLoadingSongs.value = true
        try {
            const response = await api.post<ApiResponse<Song[]>>(
                '/song/listBySingerId',
                {
                    singer_id: singerId,
                },
            )
            return response.data.data // ←ここで中身を剥がす
        } finally {
            isLoadingSongs.value = false
        }
    }

    const createSong = async (
        songData: SongFormInput,
    ): Promise<ApiResponse<Song>> => {
        const response = await apiAuth.post<ApiResponse<Song>>(
            '/song/create',
            songData,
        )
        return response.data
    }

    const editSong = async (
        songData: SongFormInput,
    ): Promise<ApiResponse<Song>> => {
        const response = await apiAuth.post<ApiResponse<Song>>(
            '/song/edit',
            songData,
        )
        return response.data
    }

    const deleteSong = async (
        songId: number,
    ): Promise<ApiResponse<{ id: number }>> => {
        const response = await apiAuth.post<ApiResponse<{ id: number }>>(
            '/song/delete',
            { id: songId },
        )
        return response.data
    }

    return {
        isLoadingSongs,
        fetchSongsBySingerId,
        createSong,
        editSong,
        deleteSong,
    }
}
