<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useAdminAuthStore } from '@/stores/admin_auth'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
const { width, height, deviceType } = useWindowSizeAndDevice()
import { useSingerList } from '@/composables/useSingerList'
import { useSongList } from '@/composables/useSongList'

const adminAuthStore = useAdminAuthStore()

const { singerList, fetchSingerList, isLoadingSingerList } = useSingerList()
const {
    songList,
    fetchSongListBySingerId,
    isLoadingSongList,
    createSongAndReload,
    isCreatingSong,
    isEditingSong,
    editSongAndReload,
    isDeletingSong,
    deleteSongAndReload,
} = useSongList()

onMounted(async () => {
    fetchSingerList()
})

const activeSingerId = ref<number | null>(null)
const activeSingerName = ref('')

// 選択時の処理
const handleSingerChange = (id: number) => {
    const selected = singerList.value.find((s) => s.id === id)
    if (selected) {
        activeSingerId.value = selected.id
        activeSingerName.value = selected.name
    } else {
        activeSingerId.value = null
        activeSingerName.value = ''
    }
}

// activeSingerId が変わったら曲一覧取得
watch(activeSingerId, (newId) => {
    songList.value = []
    if (newId !== null) {
        fetchSongListBySingerId(newId)
    } else {
        songList.value = []
    }
})

const isOpentest = ref(false)
const opendTest = () => {
    setTimeout(() => {
        isOpentest.value = true
    }, 1000)
}
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${height}px` }"
        :data-device="deviceType"
        :data-windowWidth="width"
    >
        <el-scrollbar>
            <header class="Page__header">
                <label class="Page__singerSelectWrap"
                    ><span class="Page__singerSelectText">歌手を選択：</span>
                    <el-select
                        class="Page__singerSelect"
                        v-model="activeSingerId"
                        placeholder="選択してください"
                        :disabled="isLoadingSingerList"
                        @change="handleSingerChange"
                    >
                        <el-option
                            v-for="singer in singerList"
                            :key="singer.id"
                            :label="singer.name"
                            :value="singer.id"
                        /> </el-select
                ></label>

                <router-link
                    v-if="adminAuthStore.level === 0"
                    class="Page__adminLink"
                    to="/admin"
                    >管理画面へ</router-link
                >
            </header>
            <h1 class="Page__h1">
                {{ activeSingerName ? activeSingerName : '未選択' }}
            </h1>
            <main>
                <div
                    v-if="isLoadingSingerList || isLoadingSongList"
                    class="Page__loadingWrap"
                >
                    <Loading text="" :isOverlay="false" />
                </div>

                <SingerSongAccordionList
                    v-if="singerList && songList"
                    :items="songList"
                />
                <p
                    v-if="
                        activeSingerId && !songList.length && !isLoadingSongList
                    "
                >
                    曲が登録されていません
                </p>
            </main>
        </el-scrollbar>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    width: 100%;
    overflow: hidden;
    height: 100%;

    &__header {
        display: flex;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid #ccc;
    }

    &__singerSelectWrap {
        display: flex;
        align-items: center;
    }

    &__singerSelectText {
        flex-shrink: 0;
    }

    & &__singerSelect {
        width: 200px;
    }

    &__adminLink {
        margin-left: auto;
    }

    &__h1 {
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        background-color: #111;
        color: #fff;
        padding: 8px;
        border-bottom: 1px solid #111;
        position: sticky;
        top: 0;
        left: 0;
        z-index: 1;
    }

    &__loadingWrap {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 36px;
    }

    @media screen and (max-width: 740px) {
    }
}
</style>
