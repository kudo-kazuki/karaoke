import type { SongFormInput } from '@/types'

export const MAX_LYRICS_LENGTH = 8000 // 歌詞の最大文字数

export const EMPTY_DATA: SongFormInput = {
    id: 0,
    singer_id: 0,
    name: '',
    youtube_url: '',
    release_date: '',
    lyrics: '',
}

// ソート条件（デフォルト: id昇順）
export const SONG_SORT_KEY = {
    ID_ASC: 'id-asc',
    ID_DESC: 'id-desc',
    UPDATED_ASC: 'updated-asc',
    UPDATED_DESC: 'updated-desc',
    NAME_ASC: 'name-asc',
    NAME_DESC: 'name-desc',
    ORIGINAL: 'original',
} as const

// ソートオプション定義
export const SONG_SORT_OPTION = [
    { label: 'ID昇順', value: SONG_SORT_KEY.ID_ASC },
    { label: 'ID降順', value: SONG_SORT_KEY.ID_DESC },
    { label: '更新日昇順', value: SONG_SORT_KEY.UPDATED_ASC },
    { label: '更新日降順', value: SONG_SORT_KEY.UPDATED_DESC },
    { label: '曲名昇順', value: SONG_SORT_KEY.NAME_ASC },
    { label: '曲名降順', value: SONG_SORT_KEY.NAME_DESC },
    { label: 'オリジナル順', value: SONG_SORT_KEY.ORIGINAL },
] as const
