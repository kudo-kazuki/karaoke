<script setup lang="ts">
import { ref, computed } from 'vue'
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

// ソート条件（デフォルト: id昇順）
const sortKey = ref<
    | 'id-asc'
    | 'id-desc'
    | 'updated-asc'
    | 'updated-desc'
    | 'name-asc'
    | 'name-desc'
>('id-asc')

// ソートオプション定義
const sortOptions = [
    { label: 'ID昇順', value: 'id-asc' },
    { label: 'ID降順', value: 'id-desc' },
    { label: '更新日昇順', value: 'updated-asc' },
    { label: '更新日降順', value: 'updated-desc' },
    { label: '曲名昇順', value: 'name-asc' },
    { label: '曲名降順', value: 'name-desc' },
] as const

// ソート済みリスト
const sortedItems = computed(() => {
    const items = [...props.items] // 破壊しないようコピー
    switch (sortKey.value) {
        case 'id-asc':
            return items.sort((a, b) => a.id - b.id)
        case 'id-desc':
            return items.sort((a, b) => b.id - a.id)
        case 'updated-asc':
            return items.sort(
                (a, b) =>
                    new Date(a.updated_at).getTime() -
                    new Date(b.updated_at).getTime(),
            )
        case 'updated-desc':
            return items.sort(
                (a, b) =>
                    new Date(b.updated_at).getTime() -
                    new Date(a.updated_at).getTime(),
            )
        case 'name-asc':
            return items.sort((a, b) =>
                (a.name ?? '').localeCompare(b.name ?? ''),
            )
        case 'name-desc':
            return items.sort((a, b) =>
                (b.name ?? '').localeCompare(a.name ?? ''),
            )
        default:
            return items
    }
})
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
                <el-select
                    class="SingersSongList__sortSelect"
                    v-model="sortKey"
                    placeholder="ソート"
                    style="margin-left: 12px; width: 160px"
                >
                    <el-option
                        v-for="option in sortOptions"
                        :key="option.value"
                        :label="option.label"
                        :value="option.value"
                    />
                </el-select>
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
                            v-for="item in sortedItems"
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
        align-items: center;
        column-gap: 16px;
    }

    &__loadingWrap {
        text-align: center;
        display: flex;
        justify-content: center;
        padding: 20px 12px;
    }

    & &__sortSelect {
        margin-left: auto !important;
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
        width: 40%;
        font-weight: bold;
        flex-shrink: 0;
    }

    &__buttons {
        margin: 20px 0 0;
        display: flex;
        align-items: center;
        column-gap: 12px;
    }

    @media screen and (max-width: 740px) {
        & &__sortSelect {
            width: 124px !important;
        }
    }
}
</style>
