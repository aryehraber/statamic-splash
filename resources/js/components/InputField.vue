<template>
  <!-- Empty state. Mirrors the control panel's own asset fieldtype picker. -->
  <div
    v-if="!value"
    class="flex items-center gap-3 rounded-xl border border-gray-300 p-2 dark:border-gray-700 dark:bg-gray-850"
  >
    <Button
      icon="folder-open"
      :text="__('Browse Unsplash')"
      @click="$emit('open')"
    />
  </div>

  <!-- Selected state. Mirrors AssetTile: the controls sit in a hover overlay. -->
  <div
    v-else
    class="group relative w-full max-w-2xs overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700"
  >
    <img
      :src="thumbUrl"
      :alt="value.description"
      :title="value.description"
      class="block w-full"
    />

    <div
      class="absolute inset-0 flex items-center justify-center opacity-0 duration-100 group-hover:opacity-100"
    >
      <Button
        size="sm"
        icon="x"
        :aria-label="__('Remove')"
        @click="$emit('remove')"
      />
    </div>

    <div
      v-if="value.description"
      class="truncate border-t border-gray-300 px-2 py-1 text-xs text-gray-600 dark:border-gray-700 dark:text-gray-400"
      :title="value.description"
      v-text="value.description"
    />
  </div>
</template>

<script>
import { Button } from '@statamic/cms/ui'

export default {
  components: { Button },

  props: ['value'],

  emits: ['open', 'remove'],

  computed: {
    thumbUrl() {
      return `${this.value.urls.raw}&crop=entropy&cs=tinysrgb&fit=max&q=80&w=400`
    },
  },
}
</script>
