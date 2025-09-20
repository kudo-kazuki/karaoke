<script setup lang="ts">
import iconClose from '@/assets/images/close2.png'
import { ElScrollbar } from 'element-plus'

const isOpen = defineModel<boolean>('isOpen', { default: false })

const emit = defineEmits<{
    (e: 'afterClose'): void
}>()

const close = () => {
    isOpen.value = false
}

const showScrollButton = ref(false)
// 現在のスクロールTopを常に記憶
const currentTop = ref(0)

// スクロール対象の el-scrollbar インスタンス
const scrollbarRef = ref<InstanceType<typeof ElScrollbar> | null>(null)

// el-scrollbar の @scroll で受け取れるイベント引数
type ScrollEvt = { scrollTop: number; scrollLeft: number }

// スクロール時：位置を記録
const onScroll = ({ scrollTop }: ScrollEvt) => {
    console.log('g')
    currentTop.value = scrollTop
}

// 位置を監視して、0なら非表示／それ以外は表示（しきい値は好みで）
const THRESHOLD = 1 // 1px でも動いたら表示。100 とかに変えてもOK
watch(currentTop, (t) => {
    showScrollButton.value = t > THRESHOLD
})

// アニメーションで最上部へ
const goToPageTop = () => {
    // 1) ネイティブの smooth が使える環境なら scrollTo を優先
    //    （Element Plusは内部で wrapRef.scrollTo を呼ぶので効く環境が多い）
    if (scrollbarRef.value?.wrapRef?.scrollTo) {
        scrollbarRef.value.wrapRef.scrollTo({ top: 0, behavior: 'smooth' })
        return
    }
    // 2) フォールバック：requestAnimationFrame + setScrollTop で自前アニメ
    smoothSetScrollTop(0, 350) // 350ms でスッと上がる
}

// setScrollTop を使った手作りスムーススクロール
const smoothSetScrollTop = (to: number, duration = 300) => {
    const start = currentTop.value
    const startTime = performance.now()

    const easeOutCubic = (x: number) => 1 - Math.pow(1 - x, 3)

    const frame = (now: number) => {
        const prog = Math.min(1, (now - startTime) / duration)
        const eased = easeOutCubic(prog)
        const value = Math.round(start + (to - start) * eased)
        scrollbarRef.value?.setScrollTop?.(value)
        if (prog < 1) requestAnimationFrame(frame)
    }

    requestAnimationFrame(frame)
}

// 初期状態の反映（マウント時に現在位置を一度見ておく）
onMounted(() => {
    const initTop = scrollbarRef.value?.wrapRef?.scrollTop ?? 0
    currentTop.value = initTop
})
</script>

<template>
    <transition name="slide" @after-leave="$emit('afterClose')">
        <section v-show="isOpen" class="SlideContents">
            <div class="SlideContents__inner">
                <header class="SlideContents__header">
                    <button class="SlideContents__closeButton" @click="close">
                        <img
                            class="SlideContents__icon"
                            :src="iconClose"
                            alt="閉じる"
                        />
                        閉じる
                    </button>
                </header>

                <el-scrollbar ref="scrollbarRef" @scroll="onScroll">
                    <div class="SlideContents__container">
                        <slot />
                    </div>
                </el-scrollbar>
            </div>

            <ScrollToTopButton
                v-model:isVisible="showScrollButton"
                @clicked="goToPageTop()"
            />
        </section>
    </transition>
</template>

<style lang="scss" scoped>
.SlideContents {
    position: fixed;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
    z-index: 999;

    &__header {
        padding: 16px 16px;
        border-bottom: 1px solid #333;
    }

    &__closeButton {
        display: flex;
        column-gap: 4px;
        align-items: center;
        height: 32px;
        line-height: 0;
        margin-left: auto;
    }

    &__icon {
        height: 100%;
    }

    &__inner {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    &__container {
        padding: 16px 16px 60px;
    }

    @media screen and (max-width: 740px) {
        &__header {
            padding: 12px;
        }

        &__closeButton {
            height: 24px;
        }

        &__container {
            padding: 12px 12px 52px;
        }
    }
}

/* --- transition アニメーション定義 --- */
.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

.slide-enter-to,
.slide-leave-from {
    transform: translateX(0);
    opacity: 1;
}

.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}
</style>
