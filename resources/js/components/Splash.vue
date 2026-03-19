<template>
  <div class="text-sm" v-if="! meta.access_key">
    <code>Missing Unsplash API Access Key</code>
  </div>

  <div v-else>
    <input-field
      :value="value"
      @open="openBrowser"
      @remove="removeImage"
    />

    <stack
      name="unsplash-browser"
      @closed="closeBrowser"
      v-if="showBrowser"
    >
      <div class="flex flex-col h-full bg-white">

        <div class="relative flex flex-col h-full">
          <image-viewer
            :image="selectedImage"
            :thumb-sizes="thumbSizes"
            @close="closeImage"
            v-if="selectedImage"
          />

          <div class="flex items-center justify-between w-full p-2 bg-white">
            <data-list-search v-model="searchQuery" placeholder="Search Unsplash..." />

            <div class="hidden md:flex ml-1">
              <button
                class="btn btn-sm"
                @click="selectedThumbSize = selectedThumbSize === 'large' ? 'small' : 'large'"
              >
                <svg-icon
                  class="h-4 w-4"
                  :name="selectedThumbSize === 'large' ? 'shrink-all' : 'expand'"
                />
              </button>
            </div>
          </div>

          <div
            class="relative z-10 flex-1 w-full h-full"
            :class="{ 'overflow-y-scroll': ! selectedImage }"
            ref="imageContainer"
          >
            <div class="absolute inset-0 p-2">
              <div class="asset-grid-listing" :class="thumbGrid">
                <thumb
                  v-for="image in filteredImages"
                  :key="image.id"
                  :image="image"
                  :sizes="thumbSizes"
                  @open="openImage(image)"
                />
              </div>

              <div class="z-20 p-2 pb-4 text-center" v-if="canLoadMore">
                <button
                  class="btn"
                  @click="loadMore"
                  :disabled="loading"
                  v-text="loading ? 'Loading...' : 'Load More'"
                  ref="loadMoreButton"
                />
              </div>

              <div
                class="pb-5 font-medium text-center text-red"
                v-text="`Unsplash Error: ${error.data} (${error.status})`"
                v-if="error"
              />
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end z-20 p-2 bg-gray-200 border-t">
          <button class="btn" @click="closeBrowser">
            Cancel
          </button>

          <button class="btn-primary ml-1" @click="select" :disabled="! selectedImage">
            Select
          </button>
        </div>

      </div>
    </stack>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import { useFieldtype } from '../composables/fieldtype'
import Thumb from './Thumb.vue'
import InputField from './InputField.vue'
import ImageViewer from './ImageViewer.vue'

// Use Statamic's fieldtype composable
const { meta, config, value } = useFieldtype()

// Reactive data
const loading = ref(false)
const error = ref(null)
const showBrowser = ref(false)
const searchQuery = ref('')
const searchPage = ref(1)
const images = ref([])
const hasNextPage = ref(null)
const selectedImage = ref(null)
const selectedThumbSize = ref('small')
const imageContainer = ref(null)
const loadMoreButton = ref(null)

// Emits
const emit = defineEmits(['input'])

// Computed properties
const filteredImages = computed(() => {
  const ids = []
  return images.value.filter(({ id }) => {
    if (ids.indexOf(id) !== -1) return
    ids.push(id)
    return true
  })
})

const canLoadMore = computed(() => {
  if (searchPage.value === 1 && loading.value) return
  return searchQuery.value
    ? images.value.length && hasNextPage.value
    : true
})

const thumbGrid = computed(() => {
  const sizes = {
    small: 'splash-grid-cols-2 sm:splash-grid-cols-3 md:splash-grid-cols-4 lg:splash-grid-cols-5 xl:splash-grid-cols-6',
    large: 'splash-grid-cols-2 sm:splash-grid-cols-3',
  }
  return sizes[selectedThumbSize.value]
})

const thumbSizes = computed(() => {
  const sizes = {
    small: '(max-width: 575px) 50vw, (max-width: 768px) 25vw, (min-width: 1000px) 15vw',
    large: '(max-width: 575px) 50vw, (max-width: 768px) 33vw, (min-width: 1000px) 25vw',
  }
  return sizes[selectedThumbSize.value]
})

// Methods
const search = async (loadMore = false) => {
  if (loading.value) return

  loading.value = true
  error.value = null

  if (loadMore) {
    searchPage.value++
  }

  const baseUrl = 'https://api.unsplash.com'
  const url = searchQuery.value ? '/search/photos' : '/photos'

  const params = {
    client_id: meta.value.access_key,
    query: searchQuery.value,
    page: searchPage.value,
    per_page: 30,
  }

  try {
    const response = await window.Statamic.$axios.get(`${baseUrl}${url}`, { params })
    const data = response.data
    const results = data.results || data

    images.value = loadMore ? images.value.concat(results) : results
    hasNextPage.value = data.total_pages ? data.total_pages > searchPage.value : null
  } catch (err) {
    if (err.response) {
      const { data, status } = err.response
      error.value = { data, status }
    }
  } finally {
    loading.value = false
  }
}

const loadMore = () => {
  search(true)
}

const openBrowser = () => {
  showBrowser.value = true
  search()
}

const closeBrowser = () => {
  showBrowser.value = false
  images.value = []
  searchQuery.value = ''
  searchPage.value = 1
  hasNextPage.value = null
  selectedImage.value = null
}

const openImage = (image) => {
  selectedImage.value = image
}

const closeImage = () => {
  selectedImage.value = null
}

const select = () => {
  emit('input', selectedImage.value)
  pingUnsplash()
  closeBrowser()
}

const removeImage = () => {
  selectedImage.value = null
  emit('input', null)
}

const setDefaultThumbSize = () => {
  if (config.value.thumb_size !== undefined) {
    selectedThumbSize.value = config.value.thumb_size
  } else if (meta.value.default_thumb_size !== undefined) {
    selectedThumbSize.value = meta.value.default_thumb_size
  }
}

const initInfiniteScroll = () => {
  if (!imageContainer.value) return

  imageContainer.value.addEventListener('scroll', _.throttle(() => {
    if (loading.value) return

    const offset = 300
    const button = loadMoreButton.value

    if (!button) return

    if (imageContainer.value.scrollTop + imageContainer.value.clientHeight >= imageContainer.value.scrollHeight - offset) {
      loadMore()
    }
  }, 250))
}

const pingUnsplash = async () => {
  if (!selectedImage.value) return

  const params = { client_id: meta.value.access_key }
  try {
    await window.Statamic.$axios.get(selectedImage.value.links.download_location, { params })
  } catch (error) {
    // Silently fail for ping
  }
}

// Watchers
watch(showBrowser, (show) => {
  if (show) {
    nextTick(() => {
      setTimeout(initInfiniteScroll, 100)
    })
  }
})

watch(searchQuery, () => {
  images.value = []
  searchPage.value = 1
  hasNextPage.value = null
  selectedImage.value = null
  search()
})

// Lifecycle
onMounted(() => {
  setDefaultThumbSize()
})
</script>

<style>
  .-z-1 { z-index: -1 !important; }
  .lazyloaded { transition: all 0.3s ease; }
</style>
