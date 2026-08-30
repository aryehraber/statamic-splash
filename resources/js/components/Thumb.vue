<template>
  <button
    type="button"
    class="group relative block w-full cursor-zoom-in overflow-hidden rounded-lg"
    style="padding-top: 75%"
    :title="image.description"
    @click="$emit('open')"
  >
    <!-- Unsplash gives every photo an average colour; use it as the placeholder
      so the grid does not flash white while thumbnails stream in. -->
    <div class="absolute inset-0 rounded-lg opacity-50" :style="{ background: image.color }" />

    <div class="absolute inset-0 flex items-center justify-center">
      <Icon name="loading" class="size-5 text-gray-500 opacity-50" />
    </div>

    <img
      class="lazyload absolute inset-0 size-full rounded-lg object-cover object-center"
      :data-srcset="thumbUrl"
      :data-sizes="sizes"
      :alt="image.description"
    />

    <div
      class="absolute inset-0 rounded-lg ring-inset ring-blue-500 group-hover:ring-2"
      aria-hidden="true"
    />
  </button>
</template>

<script>
import { Icon } from '@statamic/cms/ui'

export default {
  components: { Icon },

  props: ['image', 'sizes'],

  emits: ['open'],

  computed: {
    thumbUrl() {
      const widths = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200]

      return widths.map((width) => `${this.image.urls.raw}&q=60&auto=format&w=${width} ${width}w`).join(', ')
    },
  },
}
</script>
