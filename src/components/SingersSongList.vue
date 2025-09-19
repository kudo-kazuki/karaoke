<script setup lang="ts">
import type { Song, SongFormInput } from '@/types'

interface Props {
    items: Song[]
    singerId: number | null
    singerName: string
    isLoading?: boolean
}

const props = withDefaults(defineProps<Props>(), {})

const emit = defineEmits([
    'clickedNewSongCreate',
    'clickedEdit',
    'clickedDelete',
])

const clickedNewSongCreate = () => {
    emit('clickedNewSongCreate', props.singerId)
}

const clickedEdit = (data: SongFormInput) => {
    emit('clickedEdit', data)
}

const clickedDelete = (data: { id: number; name: string }) => {
    emit('clickedDelete', data)
}
</script>

<template>
    <div class="SingersSongList">
        <div class="SingersSongList__inner">
            <header class="SingersSongList__header" ref="headerRef">
                <h1>{{ singerName }}の曲一覧</h1>
                <Button
                    class="Page__createButton"
                    text="新規追加"
                    color="blue"
                    @click="clickedNewSongCreate"
                />
            </header>
            <el-scrollbar>
                <div class="SingersSongList__content">
                    <div v-if="isLoading" class="SingersSongList__loadingWrap">
                        <Loading text="" :isOverlay="false" />
                    </div>

                    <ul
                        v-if="items.length && !isLoading"
                        class="SingersSongList__items"
                    >
                        <li
                            v-for="item in items"
                            :key="item.id"
                            class="SingersSongList__item"
                        >
                            <div class="SingersSongList__name">
                                {{ item.name }}
                                <div class="SingersSongList__buttons">
                                    <Button
                                        class="Page__createButton"
                                        text="編集"
                                        color="blue"
                                        @click="clickedEdit(item)"
                                    />
                                    <Button
                                        class="Page__createButton"
                                        text="削除"
                                        color="red"
                                        @click="
                                            clickedDelete({
                                                id: item.id,
                                                name: item.name,
                                            })
                                        "
                                    />
                                </div>
                            </div>
                            <div class="SingersSongList__thumbnail">
                                <YoutubeThumbnail
                                    :videoId="item.youtube_url ?? ''"
                                    size="mqdefault"
                                />
                            </div>
                        </li>
                    </ul>

                    <p v-if="!items.length && !isLoading">
                        曲が登録されていません。
                    </p>
                </div>
            </el-scrollbar>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.SingersSongList {
    &__header {
        display: flex;
        column-gap: 16px;
    }

    &__loadingWrap {
        text-align: center;
        display: flex;
        justify-content: center;
        padding: 20px 12px;
    }

    &__items {
        display: grid;
        grid-template-columns: repeat(3, 1fr); // デフォルトは4列
        gap: 12px; // 各アイテムの間隔
        padding: 6px;
        margin-top: 12px;

        @media screen and (max-width: 1300px) {
            grid-template-columns: repeat(2, 1fr);
        }

        @media screen and (max-width: 800px) {
            grid-template-columns: 1fr; // 1列
        }
    }

    &__item {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #ccc;
        padding: 16px;
        column-gap: 12px;
        box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
        background-color: #fff;
        border-radius: 3px;
    }

    &__name {
        width: 70%;
        font-weight: bold;
    }

    &__buttons {
        margin: 20px 0 0;
        display: flex;
        align-items: center;
        column-gap: 12px;
    }

    @media screen and (max-width: 740px) {
    }
}
</style>
