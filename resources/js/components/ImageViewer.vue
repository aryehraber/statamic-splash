<template>
  <div class="absolute inset-0 z-10 bg-white p-2 dark:bg-gray-900">
    <div class="relative size-full cursor-zoom-out" @click="$emit('close')">
      <div class="absolute inset-0 flex items-center justify-center">
        <Icon name="loading" class="size-6 text-gray-500" />
      </div>

      <!-- The thumbnail sits underneath at low resolution so something is
        visible immediately, with the full-size image lazyloaded over it. -->
      <img
        class="absolute size-full object-contain"
        :srcset="thumbUrl"
        :sizes="thumbSizes"
        :alt="image.description"
      />

      <img
        class="lazyload absolute size-full object-contain"
        :data-srcset="imageUrl"
        data-sizes="(min-width: 1000px) 60vw, (min-width: 1200px) 70vw, (min-width: 1400px) 80vw, 100vw"
        :alt="image.description"
      />
    </div>
  </div>
</template>

<script>
import { Icon } from '@statamic/cms/ui'

const srcset = (image, widths) =>
  widths.map((width) => `${image.urls.raw}&q=60&auto=format&w=${width} ${width}w`).join(', ')

export default {
  components: { Icon },

  props: ['image', 'thumbSizes'],

  emits: ['close'],

  computed: {
    thumbUrl() {
      return srcset(this.image, [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200])
    },
    imageUrl() {
      return srcset(this.image, [
        100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200, 1296, 1400, 1600, 1800, 2000, 2200,
        2400,
      ])
    },
  },
}
</script>
