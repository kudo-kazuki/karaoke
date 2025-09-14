import { ref } from 'vue'
import { useSingerApi } from '@/composables/useSingerApi'
import type { Singer } from '@/types'

const { fetchSingers } = useSingerApi()

export function useSingerList() {
    const singerList = ref<Singer[]>([])

    const fetchSingerList = async () => {
        fetchSingers().then((data) => {
            singerList.value = data
        })
    }

    return {
        singerList,
        fetchSingerList,
    }
}
