<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
const { width, height, deviceType } = useWindowSizeAndDevice()
import { useSingerList } from '@/composables/useSingerList'
import { useSongList } from '@/composables/useSongList'

const { singerList, fetchSingerList, isLoadingSingerList } = useSingerList()
const { songList, fetchSongListBySingerId, isLoadingSongList } = useSongList()

onMounted(async () => {
    fetchSingerList()
})

const activeSingerId = ref<number | null>(null)
const activeSingerName = computed(() => {
    if (activeSingerId.value === null) return ''
    const singer = singerList.value.find((s) => s.id === activeSingerId.value)
    return singer ? singer.name : ''
})

// activeSingerId が変わったら曲一覧取得
watch(activeSingerId, (newId) => {
    if (newId !== null) {
        fetchSongListBySingerId(newId)
    } else {
        songList.value = []
    }
})

const isOpenSingerSongs = ref(false)
const openSingersSongs = (singerId: number) => {
    if (activeSingerId.value === singerId) {
        closeSingersSongs()
        return false
    }
    activeSingerId.value = singerId
    isOpenSingerSongs.value = true
}
const closeSingersSongs = () => {
    activeSingerId.value = null
    isOpenSingerSongs.value = false
}
</script>

<template>
    <div
        class="Page"
        :style="{ height: `${height}px` }"
        :data-device="deviceType"
        :data-windowWidth="width"
    >
        <div class="Page__inner">
            <header class="Page__header" ref="headerRef">
                <h1>曲管理ページ</h1>
                <router-link to="/admin/" class="Page__returnPage"
                    >戻る</router-link
                >
            </header>
            <el-scrollbar>
                <div class="Page__content">
                    <SingerButtonList
                        v-if="!isLoadingSingerList"
                        :items="singerList"
                        :activeId="activeSingerId"
                        @clickedSinger="openSingersSongs"
                    />
                    <Loading
                        v-if="isLoadingSingerList"
                        text=""
                        :isOverlay="false"
                    />
                </div>
            </el-scrollbar>
        </div>

        <SlideContents
            v-model:isOpen="isOpenSingerSongs"
            @afterClose="closeSingersSongs"
        >
            <SingersSongList
                :items="songList"
                :singerId="activeSingerId"
                :singerName="activeSingerName"
                :isLoading="isLoadingSongList"
            />
        </SlideContents>
    </div>
</template>

<style lang="scss" scoped>
.Page {
    width: 100%;
    overflow: hidden;
    height: 100%;

    &__inner {
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    &__header {
        display: flex;
        align-items: center;
        column-gap: 12px;
        padding: 20px;
    }

    &__content {
        height: 100%;
    }

    &__returnPage {
        margin-left: auto;
    }

    &__createButtonWrap {
        text-align: center;
    }

    &__editItems {
        display: flex;
        flex-direction: column;
        row-gap: 12px;
    }

    &__editItem {
        display: flex;
        align-items: center;
    }

    &__editLabel {
        width: 180px;
    }

    &__LabelSmall {
        display: block;
        font-size: 11px;
    }

    &__errorMessage {
        color: crimson;
        text-align: center;
        margin: 10px 0;
    }

    @media screen and (max-width: 740px) {
    }
}
</style>
