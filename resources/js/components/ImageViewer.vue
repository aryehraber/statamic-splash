<template>
  <div class="absolute inset-0 z-20 bg-white p-2" >
    <div
      class="relative w-full h-full"
      style="cursor: zoom-out;"
      @click="$emit('close')"
    >
      <img
        class="absolute z-10 w-full h-full lazyload"
        style="object-fit: contain;"
        :data-srcset="imageUrl"
        data-sizes="(min-width: 1000px) 60vw, (min-width: 1200px) 70vw, (min-width: 1400px) 80vw, 100vw"
        :alt="image.description"
      >

      <img
        class="absolute z-0 w-full h-full"
        style="object-fit: contain;"
        :srcset="thumbUrl"
        :sizes="thumbSizes"
        :alt="image.description"
      >

      <loading-graphic class="absolute inset-0 z-5 flex flex-col items-center justify-center text-center" text="" />
    </div>
  </div>
</template>

<script>
export default {
  props: ['image', 'thumbSizes'],

  computed: {
    thumbUrl() {
      const widths = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200]

      return widths.map(width => `${this.image.urls.raw}&q=60&auto=format&w=${width} ${width}w`).join(', ')
    },
    imageUrl() {
      const widths = [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200, 1296, 1400, 1600, 1800, 2000, 2200, 2400]

      return widths.map(width => `${this.image.urls.raw}&q=60&auto=format&w=${width} ${width}w`).join(', ')
    },
  },
}
</script>
