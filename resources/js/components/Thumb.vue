<template>
  <div class="mb-2 px-1 group" :class="width">
    <button
      class="w-full relative text-center ratio-4:3"
      style="cursor: zoom-in;"
      @click="$emit('open')"
    >
      <div class="absolute pin z-0 flex items-center justify-center">
        <div
          class="absolute pin -z-1 rounded opacity-75"
          :style="{ background: image.color }"
        />

        <loading-graphic class="absolute pin -z-1 flex flex-col items-center justify-center text-center opacity-25" text="" />

        <img
          class="asset-thumbnail w-full h-full rounded lazyload"
          style="object-fit: cover; object-position: center;"
          :data-srcset="thumbUrl"
          :data-sizes="sizes"
          :alt="image.description"
        >
      </div>
    </button>

    <div
      class="text-3xs text-center text-grey-70 pt-sm w-full text-truncate"
      :title="image.description"
      v-text="image.description"
      v-if="image.description"
    />
  </div>
</template>

<script>
export default {
  props: ['image', 'width', 'sizes'],

  computed: {
    thumbUrl() {
      const widths = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200]

      return widths.map(width => `${this.image.urls.raw}&q=60&auto=format&w=${width} ${width}w`).join(', ')
    },
  },
}
</script>
