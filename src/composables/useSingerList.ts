import { ref } from 'vue'
import { useSingerApi } from '@/composables/useSingerApi'
import type { Singer } from '@/types'

const { fetchSingers } = useSingerApi()

export function useSingerList() {
    const singerList = ref<Singer[]>([])
    const isLoadingSingerList = ref(false)

    const fetchSingerList = async () => {
        isLoadingSingerList.value = true

        fetchSingers().then((data) => {
            singerList.value = data
            isLoadingSingerList.value = false
        })
    }

    return {
        singerList,
        fetchSingerList,
        isLoadingSingerList,
    }
}
