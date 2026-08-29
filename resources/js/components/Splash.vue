<template>
  <Alert v-if="!meta.access_key" variant="error" :text="__('Missing Unsplash API Access Key')" />

  <div v-else>
    <InputField :value="value" @open="openBrowser" @remove="removeImage" />

    <Stack v-model:open="showBrowser" inset :show-close-button="false" @closed="resetBrowser">
      <div class="flex h-full min-h-0 flex-col bg-white dark:bg-gray-900">
        <div class="relative flex min-h-0 flex-1 flex-col">
          <ImageViewer
            v-if="selectedImage"
            :image="selectedImage"
            :thumb-sizes="thumbSizes"
            @close="closeImage"
          />

          <div class="flex items-center gap-3 border-b border-gray-200 p-3 dark:border-gray-700">
            <Input
              v-model="searchQuery"
              class="flex-1"
              icon="magnifying-glass"
              :placeholder="__('Search Unsplash...')"
            />

            <Button
              :icon="selectedThumbSize === 'large' ? 'shrink' : 'expand-all'"
              :aria-label="__('Toggle thumbnail size')"
              @click="selectedThumbSize = selectedThumbSize === 'large' ? 'small' : 'large'"
            />
          </div>

          <div ref="imageContainer" class="flex-1 overflow-y-auto p-3">
            <div class="grid gap-3" :data-splash-grid="selectedThumbSize">
              <Thumb
                v-for="image in filteredImages"
                :key="image.id"
                :image="image"
                :sizes="thumbSizes"
                @open="openImage(image)"
              />
            </div>

            <div v-if="loading" class="flex items-center justify-center gap-2 p-4 text-sm text-gray-600 dark:text-gray-400">
              <Icon name="loading" class="size-4" />
              <span>{{ __('Loading...') }}</span>
            </div>

            <div v-else-if="canLoadMore" class="p-4 text-center">
              <Button ref="loadMoreButton" :text="__('Load More')" @click="loadMore" />
            </div>

            <Alert
              v-if="error"
              class="mt-3"
              variant="error"
              :text="`Unsplash Error: ${error.data} (${error.status})`"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-100 px-4 py-3 dark:border-gray-700 dark:bg-gray-850">
          <Button variant="ghost" :text="__('Cancel')" @click="closeBrowser" />
          <Button variant="primary" :text="__('Select')" :disabled="!selectedImage" @click="select" />
        </div>
      </div>
    </Stack>
  </div>
</template>
<script>
import { FieldtypeMixin as Fieldtype } from '@statamic/cms'
import { Alert, Button, Icon, Input, Stack } from '@statamic/cms/ui'
import Thumb from './Thumb.vue'
import InputField from './InputField.vue'
import ImageViewer from './ImageViewer.vue'

function throttle(fn, delay) {
  let lastCall = 0
  return function (...args) {
    const now = Date.now()
    if (now - lastCall >= delay) {
      lastCall = now
      fn.apply(this, args)
    }
  }
}

export default {
  mixins: [Fieldtype],

  components: {
    Thumb,
    InputField,
    ImageViewer,
    Alert,
    Button,
    Icon,
    Input,
    Stack,
  },

  data() {
    return {
      loading: false,
      error: null,
      showBrowser: false,
      searchQuery: '',
      searchPage: 1,
      searchToken: 0,
      images: [],
      hasNextPage: null,
      selectedImage: null,
      selectedThumbSize: 'small',
    }
  },

  computed: {
    filteredImages() {
      const ids = []

      return this.images.filter(({ id }) => {
        if (ids.indexOf(id) !== -1) return

        ids.push(id)

        return true
      })
    },
    canLoadMore() {
      if (this.searchPage === 1 && this.loading) return

      return this.searchQuery
        ? this.images.length && this.hasNextPage
        : true
    },
    thumbSizes() {
      const sizes = {
        small: '(max-width: 575px) 50vw, (max-width: 768px) 25vw, (min-width: 1000px) 15vw',
        large: '(max-width: 575px) 50vw, (max-width: 768px) 33vw, (min-width: 1000px) 25vw',
      }

      return sizes[this.selectedThumbSize]
    },
  },

  methods: {
    search(loadMore = false) {
      // Only load-more is guarded against a request already being in flight.
      // Guarding every search would drop the query the user actually finished
      // typing, because the debounced call lands while the previous one is
      // still open.
      if (loadMore && this.loading) return

      const token = ++this.searchToken

      this.loading = true
      this.error = null

      if (loadMore) {
        this.searchPage++
      }

      const baseUrl = 'https://api.unsplash.com'
      const url = this.searchQuery ? '/search/photos' : '/photos'

      const params = {
        client_id: this.meta.access_key,
        query: this.searchQuery,
        page: this.searchPage,
        per_page: 30,
      }

      this.$axios.get(`${baseUrl}${url}`, { params })
        .then(({ data }) => {
          // A newer search has been started since; its results win.
          if (token !== this.searchToken) return

          const results = data.results || data

          this.images = loadMore ? this.images.concat(results) : results
          this.hasNextPage = data.total_pages ? data.total_pages > this.searchPage : null
          this.loading = false
        })
        .catch(error => {
          if (token !== this.searchToken) return

          if (error.response) {
            const { data, status } = error.response

            this.error = { data, status }
          }

          this.loading = false
        })
    },
    loadMore() {
      this.search(true)
    },
    openBrowser() {
      this.showBrowser = true
      this.search()
    },
    closeBrowser() {
      this.showBrowser = false
    },
    resetBrowser() {
      this.images = []
      this.searchQuery = ''
      this.searchPage = 1
      this.hasNextPage = null
      this.selectedImage = null
    },
    openImage(image) {
      this.selectedImage = image
    },
    closeImage() {
      this.selectedImage = null
    },
    select() {
      this.update(this.selectedImage)
      this.pingUnsplash()
      this.closeBrowser()
    },
    removeImage() {
      this.selectedImage = null
      this.update(null)
    },
    setDefaultThumbSize() {
      if (this.config.thumb_size !== undefined) {
        this.selectedThumbSize = this.config.thumb_size
      } else if (this.meta.default_thumb_size !== undefined) {
        this.selectedThumbSize = this.meta.default_thumb_size
      }
    },
    initInfiniteScroll() {
      const imageContainer = this.$refs.imageContainer

      if (! imageContainer) return

      imageContainer.addEventListener('scroll', throttle(() => {
        if (this.loading) return

        const offset = 300
        const loadMoreButton = this.$refs.loadMoreButton

        if (! loadMoreButton) return

        if (imageContainer.scrollTop + imageContainer.clientHeight >= imageContainer.scrollHeight - offset) {
          this.loadMore()
        }
      }, 250))
    },
    runSearch() {
      this.images = []
      this.searchPage = 1
      this.hasNextPage = null
      this.selectedImage = null

      this.search()
    },
    pingUnsplash() {
      if (! this.selectedImage) return

      const params = { client_id: this.meta.access_key }

      this.$axios.get(this.selectedImage.links.download_location, { params })
    },
  },

  watch: {
    showBrowser(show) {
      if (show) {
        setTimeout(this.initInfiniteScroll, 100)
      }
    },
    searchQuery() {
      clearTimeout(this.searchDebounce)
      this.searchDebounce = setTimeout(() => this.runSearch(), 300)
    },
  },

  created() {
    this.setDefaultThumbSize()
  }
}
</script>

<style>
/*
 * Deliberately unlayered.
 *
 * The control panel declares its cascade layers as
 *
 *   @layer base, addon-theme, addon-utilities, components, utilities, ui, ui-states;
 *
 * so its own `utilities` layer sits AFTER `addon-utilities`, which is where an
 * addon's Tailwind output lands. The control panel uses grid-cols-2 itself, so
 * its plain .grid-cols-2 beats any .md\:grid-cols-4 this addon generates,
 * regardless of specificity — the grid would be stuck at two columns. The same
 * trap catches any responsive variant whose base class the control panel also
 * uses.
 *
 * Unlayered rules outrank every layered one, so the responsive grid is written
 * out here instead. Breakpoints match the control panel's Tailwind defaults.
 */
[data-splash-grid] { grid-template-columns: repeat(2, minmax(0, 1fr)); }

@media (min-width: 640px)  { [data-splash-grid="small"] { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (min-width: 768px)  { [data-splash-grid="small"] { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
@media (min-width: 1024px) { [data-splash-grid="small"] { grid-template-columns: repeat(5, minmax(0, 1fr)); } }
@media (min-width: 1280px) { [data-splash-grid="small"] { grid-template-columns: repeat(6, minmax(0, 1fr)); } }

@media (min-width: 640px)  { [data-splash-grid="large"] { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

.lazyloaded { transition: opacity 0.3s ease; }
</style>
