<script setup lang="ts">
import { computed } from 'vue'

interface Props {
    videoId: string
    size?: 'default' | 'mqdefault' | 'hqdefault' | 'sddefault' | 'maxresdefault'
    isLink?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    size: 'hqdefault',
    isLink: true,
})

const thumbnailUrl = computed(
    () => `https://img.youtube.com/vi/${props.videoId}/${props.size}.jpg`,
)

const watchUrl = computed(
    () => `https://www.youtube.com/watch?v=${props.videoId}`,
)
</script>

<template>
    <div class="YoutubeThumbnail">
        <div class="YoutubeThumbnail__thumbnailWrap">
            <img
                class="YoutubeThumbnail__thumbnail"
                :src="thumbnailUrl"
                alt="YouTube thumbnail"
            />
        </div>

        <p class="YoutubeThumbnail__link">
            <a
                v-if="isLink"
                :href="watchUrl"
                target="_blank"
                rel="noopener noreferrer"
                >{{ watchUrl }}</a
            >
        </p>
    </div>
</template>

<style lang="scss" scoped>
.YoutubeThumbnail {
    text-align: center;
    word-break: break-all;

    &__thumbnailWrap {
        line-height: 0;
    }

    &__thumbnail {
        max-width: 100%;
    }

    &__link {
        margin-top: 8px;
        text-align: left;
    }

    @media screen and (max-width: 740px) {
    }
}
</style>
