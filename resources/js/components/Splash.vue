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
            :thumb-width="thumbWidth"
            :thumb-sizes="thumbSizes"
            @close="closeImage"
            v-if="selectedImage"
          />

          <div class="flex items-center justify-between w-full p-2 bg-white">
            <data-list-search v-model="searchQuery" placeholder="Search Unsplash..." />

            <div class="hidden md:flex ml-1">
              <button
                class="btn btn-icon icon"
                :class="selectedThumbSize === 'large' ? 'icon-resize-100' : 'icon-resize-full-screen'"
                @click="selectedThumbSize = selectedThumbSize === 'large' ? 'small' : 'large'"
              />
            </div>
          </div>

          <div
            class="relative flex-1 w-full h-full"
            :class="{ 'overflow-y-scroll': ! selectedImage }"
            ref="imageContainer"
          >
            <div class="absolute inset-0 p-2">
              <div class="flex flex-wrap -mx-1">
                <thumb
                  v-for="image in filteredImages"
                  :key="image.id"
                  :image="image"
                  :width="thumbWidth"
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

        <div class="flex items-center justify-end z-20 p-2 bg-grey-20 border-t">
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

<script>
import Thumb from './Thumb.vue'
import InputField from './InputField.vue'
import ImageViewer from './ImageViewer.vue'

export default {
  mixins: [Fieldtype],

  components: {
    Thumb,
    InputField,
    ImageViewer,
  },

  data() {
    return {
      loading: false,
      error: null,
      showBrowser: false,
      searchQuery: '',
      searchPage: 1,
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
    thumbWidth() {
      const sizes = {
        small: 'w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6',
        large: 'w-1/2 sm:w-1/3',
      }

      return sizes[this.selectedThumbSize]
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
      if (this.loading) return

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
          const results = data.results || data

          this.images = loadMore ? this.images.concat(results) : results
          this.hasNextPage = data.total_pages ? data.total_pages > this.searchPage : null
          this.loading = false
        })
        .catch(error => {
          if (error.response) {
            const { data, status } = error.response

            this.error = { data, status }
          }
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
      this.$emit('input', this.selectedImage)
      this.closeBrowser()
    },
    removeImage() {
      this.selectedImage = null
      this.$emit('input', null)
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

      imageContainer.addEventListener('scroll', _.throttle(() => {
        if (this.loading) return

        const offset = 300
        const loadMoreButton = this.$refs.loadMoreButton

        if (! loadMoreButton) return

        if (imageContainer.scrollTop + imageContainer.clientHeight >= imageContainer.scrollHeight - offset) {
          this.loadMore()
        }
      }, 250))
    },
  },

  watch: {
    showBrowser(show) {
      if (show) {
        setTimeout(this.initInfiniteScroll, 100)
      }
    },
    searchQuery() {
      this.images = []
      this.searchPage = 1
      this.hasNextPage = null
      this.selectedImage = null

      this.search()
    },
  },

  created() {
    this.setDefaultThumbSize()
  }
}
</script>

<style>
  .-z-1 { z-index: -1 !important; }
  .lazyloaded { transition: all 0.3s ease; }
</style>
