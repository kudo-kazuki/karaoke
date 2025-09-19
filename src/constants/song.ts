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
