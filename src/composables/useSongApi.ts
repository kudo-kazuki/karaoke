import { ref } from 'vue'
import { api } from '@/utils/apiClient'
import type { Song } from '@/types'

interface ApiResponse<T> {
    success: boolean
    data: T
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

    return {
        isLoadingSongs,
        fetchSongsBySingerId,
    }
}
