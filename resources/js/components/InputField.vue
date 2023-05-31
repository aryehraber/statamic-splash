<template>
  <div class="assets-fieldtype-picker" v-if="! value">
    <button class="btn" @click="$emit('open')" type="button" tabindex="0">
      Browse Unsplash
    </button>
  </div>

  <div class="asset-solo-container asset-grid-listing" v-else>
    <div :title="value.description" class="asset-tile is-image" tabindex="0">
      <div class="asset-thumb-container">
        <div class="asset-thumb">
          <img :src="value.urls.small" :alt="value.description" :title="value.description">

          <div class="asset-controls">
            <button class="btn btn-icon" @click="$emit('remove')" :alt="__('Remove')">
              <svg-icon name="micro/sharp-trash" class="h-4 my-2"/>
            </button>
          </div>
        </div>
      </div>

      <div class="asset-meta">
        <div
          class="asset-filename flex-1 px-2 py-1"
          :title="value.description"
          v-text="value.description"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['value'],
}
</script>
