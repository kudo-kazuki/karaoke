<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useWindowSizeAndDevice } from '@/composables/useWindowSizeAndDevice'
const { width, height, deviceType } = useWindowSizeAndDevice()
import { useSingerList } from '@/composables/useSingerList'
import { useSongList } from '@/composables/useSongList'
import type { SubmitPayload, SongFormInput } from '@/types'
import { EMPTY_DATA } from '@/constants/song'

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

const activeSingerSongData = ref<SongFormInput>({ ...EMPTY_DATA })

// 曲の新規登録時
const isOpenNewSongCreate = ref(false)
const openNewSongCreate = (singerId: number) => {
    if (!singerId) {
        alert(`なんかおかしい ${singerId}`)
        return false
    }
    isOpenNewSongCreate.value = true
    activeSingerSongData.value.singer_id = singerId
}
const closeNewSongCreate = () => {
    isOpenNewSongCreate.value = false
}

// 曲の編集時
const isOpenSongEdit = ref(false)
const openSongEdit = (data: SongFormInput) => {
    activeSingerSongData.value = data
    isOpenSongEdit.value = true
}
const closeSongEdit = () => {
    isOpenSongEdit.value = false
}

const sendAfterServerMessage = ref('')
const sendSongData = async (data: SubmitPayload) => {
    if (!data || !activeSingerId.value) {
        return
    }

    sendAfterServerMessage.value = ''

    // 新規登録
    if (data.isCreate) {
        const res = await createSongAndReload(
            activeSingerId.value,
            data.songData,
        )
        if (res.success) {
            closeNewSongCreate()
        } else {
            sendAfterServerMessage.value = res.message ?? '登録に失敗しました'
        }
    } else {
        // 編集
        const res = await editSongAndReload(activeSingerId.value, data.songData)
        if (res.success) {
            closeSongEdit()
        } else {
            sendAfterServerMessage.value = res.message ?? '編集に失敗しました'
        }
    }
}

const activeDeleteData = ref<{ id: number; name: string }>({
    id: 0,
    name: '',
})
const isOpenSongDelete = ref(false)
const openSongDelete = (data: { id: number; name: string }) => {
    activeDeleteData.value.id = data.id
    activeDeleteData.value.name = data.name
    isOpenSongDelete.value = true
}
const closeSongDelete = () => {
    isOpenSongDelete.value = false
}
const sendDeleteData = async () => {
    if (!activeSingerId.value || !activeDeleteData.value.id) {
        return
    }
    const res = await deleteSongAndReload(
        activeSingerId.value,
        activeDeleteData.value.id,
    )
    if (res.success) {
        closeSongDelete()
    } else {
        sendAfterServerMessage.value = res.message ?? '削除に失敗しました'
    }
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
                @clickedNewSongCreate="openNewSongCreate"
                @clickedEdit="openSongEdit"
                @clickedDelete="openSongDelete"
            />
        </SlideContents>

        <!-- 新規登録 -->
        <Modal
            :title="`${activeSingerName}の曲を新規登録`"
            :isShow="isOpenNewSongCreate"
            @close="closeNewSongCreate()"
        >
            <template #body>
                <SongForm
                    isCreate
                    :singerId="activeSingerId"
                    @onSubmit="sendSongData"
                />
                <p v-if="sendAfterServerMessage" class="Page__errorMessage">
                    {{ sendAfterServerMessage }}
                </p>
            </template>
        </Modal>

        <!-- 曲編集 -->
        <Modal
            :title="`${activeSingerName}：${activeSingerSongData.name}の編集`"
            :isShow="isOpenSongEdit"
            @close="closeSongEdit()"
        >
            <template #body>
                <SongForm
                    :isCreate="false"
                    :singerId="activeSingerId"
                    :editData="activeSingerSongData"
                    @onSubmit="sendSongData"
                />
                <p v-if="sendAfterServerMessage" class="Page__errorMessage">
                    {{ sendAfterServerMessage }}
                </p>
            </template>
        </Modal>

        <!-- 曲削除 -->
        <Modal
            :title="`${activeSingerName}：${activeDeleteData.name}の削除`"
            :isShow="isOpenSongDelete"
            @close="closeSongDelete()"
        >
            <template #body>
                <div>
                    <p class="deleteText">
                        {{
                            `${activeSingerName}：${activeDeleteData.name}を削除します。`
                        }}<br />本当によろしいですか？
                    </p>
                    <div class="Page__footerButtonWrap">
                        <Button
                            class="Page__footerButton"
                            text="キャンセル"
                            color="gray"
                            @click="closeSongDelete()"
                        />
                        <Button
                            class="Page__footerButton"
                            text="削除"
                            color="red"
                            @click="sendDeleteData()"
                        />
                    </div>
                </div>
                <p v-if="sendAfterServerMessage" class="Page__errorMessage">
                    {{ sendAfterServerMessage }}
                </p>
            </template>
        </Modal>

        <Loading v-if="isCreatingSong" text="登録中" />
        <Loading v-if="isEditingSong" text="更新中" />
        <Loading v-if="isDeletingSong" text="削除中" />
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

    &__footerButtonWrap {
        display: flex;
        justify-content: center;
        column-gap: 16px;
        margin-top: 32px;
    }

    &__footerButton {
        width: 96px;
    }

    @media screen and (max-width: 740px) {
    }
}

.deleteText {
    text-align: center;
}
</style>
