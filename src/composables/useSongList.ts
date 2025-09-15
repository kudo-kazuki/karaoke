import { ref } from 'vue'
import type { Song } from '@/types'
import { useSongApi } from './useSongApi'

const { fetchSongsBySingerId } = useSongApi()

export function useSongList() {
    const songList = ref<Song[]>([])
    const isLoadingSongList = ref(false)

    const fetchSongListBySingerId = async (singerId: number) => {
        isLoadingSongList.value = true
        try {
            songList.value = await fetchSongsBySingerId(singerId)
        } finally {
            isLoadingSongList.value = false
        }
    }

    return {
        songList,
        isLoadingSongList,
        fetchSongListBySingerId,
    }
}
