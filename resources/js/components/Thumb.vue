<template>
  <div class="group">
    <button
      class="relative w-full text-center"
      style="padding-top: 75%; cursor: zoom-in;"
      :title="image.description"
      @click="$emit('open')"
    >
      <div class="absolute inset-0 flex items-center justify-center">
        <div
          class="absolute inset-0 z-0 rounded opacity-50"
          :style="{ background: image.color }"
        />

        <loading-graphic class="absolute inset-0 z-0 flex flex-col items-center justify-center text-center opacity-50" text="" />

        <img
          class="asset-thumbnail z-10 w-full h-full rounded lazyload"
          style="object-fit: cover; object-position: center;"
          :data-srcset="thumbUrl"
          :data-sizes="sizes"
          :alt="image.description"
        >
      </div>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps(['image', 'sizes'])

const thumbUrl = computed(() => {
  const widths = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200]
  return widths.map(width => `${props.image.urls.raw}&q=60&auto=format&w=${width} ${width}w`).join(', ')
})

const emit = defineEmits(['open'])
</script>
