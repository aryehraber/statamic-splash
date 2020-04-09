  <template>
  <div class="text-sm" v-if="! meta.access_key">
    <code>Missing Unsplash API Access Key</code>
  </div>

  <div v-else>
    <div class="assets-fieldtype-picker" v-if="! value">
      <button class="btn" @click="openSelector" type="button" tabindex="0">
        Browse Unsplash
      </button>
    </div>

    <div class="asset-solo-container" v-else>
      <div :title="value.description" class="asset-tile is-image" tabindex="0">
        <div class="asset-thumb-container">
          <div class="asset-thumb">
            <img :src="value.urls.small" :alt="value.description" :title="value.description">

            <div class="asset-controls">
              <button
                class="btn btn-icon icon icon-trash"
                @click="removeImage"
              />
            </div>
          </div>
        </div>
        <div class="asset-meta">
          <div
            class="asset-filename"
            :title="value.description"
            v-text="value.description"
          />
        </div>
      </div>
    </div>

    <stack
      name="unsplash-selector"
      @closed="closeSelector"
      v-if="showSelector"
    >
      <div class="flex flex-col justify-end h-full bg-white" ref="stackContainer">

        <div class="data-list-header w-full pb-2 bg-white">
          <data-list-search v-model="searchQuery" placeholder="Search Unsplash..." />
        </div>

        <div
          class="relative flex-1 w-full h-full"
          :class="{ 'overflow-y-scroll': ! zoomMode }"
          ref="imageContainer"
        >
          <div class="absolute pin z-20 bg-white p-2" v-if="zoomMode">
            <button
              class="absolute pin-t pin-r z-10 m-2 btn btn-icon icon icon-resize-100"
              @click="closeZoom"
            ></button>

            <img
              class="w-full h-full lazyload"
              style="object-fit: contain;"
              :data-src="`${selectedImage.urls.raw}${zoomParams}`"
              :alt="selectedImage.description"
            >

            <loading-graphic class="absolute pin -z-1 flex flex-col items-center justify-center text-center" />
          </div>

          <div class="absolute pin p-2">
            <div class="flex flex-wrap -mx-1">
              <div
                v-for="image in images"
                class="w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 mb-2 px-1 group"
                :class="{ selected: selectedImage && image.id === selectedImage.id }"
                @click="selectImage(image)"
              >
                <div class="w-full relative text-center cursor-pointer ratio-4:3">
                  <button
                    class="
                      btn btn-icon icon icon-resize-full-screen
                      absolute pin-t pin-r z-10 m-1 opacity-0 group-hover:opacity-100
                    "
                    @click.stop="openZoom(image)"
                  ></button>

                  <div class="absolute pin z-0 flex items-center justify-center">
                    <loading-graphic
                      class="absolute pin -z-1 flex flex-col items-center justify-center text-center opacity-25"
                      text=""
                    />

                    <img
                      class="asset-thumbnail w-full h-full rounded lazyload"
                      style="object-fit: cover; object-position: center;"
                      :data-src="`${image.urls.raw}${thumbParams}`"
                      :alt="image.description"
                    >
                  </div>
                </div>

                <div
                  class="text-3xs text-center text-grey-70 pt-sm w-full text-truncate"
                  :title="image.description"
                  v-text="image.description"
                />
              </div>
            </div>

            <div class="z-20 p-2 pb-4 text-center" v-if="images.length && hasNextPage">
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

        <div class="flex items-center justify-end z-20 p-2 bg-grey-20 border-t">
          <button
            type="button"
            class="btn"
            @click="closeSelector"
          >
            {{ __('Cancel') }}
          </button>

          <button
            type="button"
            class="btn-primary ml-1"
            @click="select"
          >
            {{ __('Select') }}
          </button>
        </div>

      </div>
    </stack>
  </div>
</template>

<script>
export default {
  mixins: [Fieldtype],

  data() {
    return {
      loading: false,
      error: null,
      showSelector: false,
      searchQuery: '',
      searchPage: 1,
      images: [],
      hasNextPage: null,
      selectedImage: null,
      zoomMode: false,
    }
  },

  computed: {
    dpr() {
      return window.devicePixelRatio || 1
    },
    zoomParams() {
      const { width } = this.$refs.stackContainer.getBoundingClientRect()

      return `&w=${width}&dpr=${this.dpr}&q=80`
    },
    thumbParams() {
      return `&w=300&h=225&dpr=${this.dpr}&q=80&fit=crop&crop=faces,center`
    },
  },

  methods: {
    search(loadMore = false) {
      if (this.loading && this.requestSource) {
        this.requestSource.cancel('Operation cancelled.')
      }

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

      this.requestSource = this.$axios.CancelToken.source()

      this.$axios.get(`${baseUrl}${url}`, { params, cancelToken: this.requestSource.token })
        .then(({ data }) => {
          const results = data.results || data

          this.images = loadMore ? this.images.concat(results) : results
          this.hasNextPage = data.total_pages ? data.total_pages > this.searchPage : null
          this.loading = false
        })
        .catch(error => {
          const { data, status } = error.response

          this.error = { data, status }
        })
    },
    loadMore() {
      this.search(true)
    },
    openSelector() {
      this.showSelector = true
      this.search()
    },
    closeSelector() {
      this.showSelector = false
      this.images = []
      this.searchQuery = ''
      this.searchPage = 1
      this.hasNextPage = null
      this.zoomMode = false
      this.requestSource = null
    },
    select() {
      this.$emit('input', this.selectedImage)
      this.closeSelector()
    },
    selectImage(image) {
      this.selectedImage = this.selectedImage !== image ? image : null
    },
    removeImage() {
      this.selectedImage = null
      this.$emit('input', null)
    },
    openZoom(image) {
      this.selectImage(image)

      this.zoomMode = true
    },
    closeZoom() {
      this.zoomMode = false
    },
    initInfiniteScroll() {
      const imageContainer = this.$refs.imageContainer

      if (! imageContainer) return

      imageContainer.addEventListener('scroll', _.throttle(() => {
        const offset = 200
        const loadMoreButton = this.$refs.loadMoreButton

        if (! loadMoreButton) return

        if (imageContainer.scrollTop + imageContainer.clientHeight >= imageContainer.scrollHeight - offset) {
          this.loadMore()
        }
      }, 250))
    },
  },

  watch: {
    showSelector(open) {
      if (open) {
        setTimeout(this.initInfiniteScroll, 100)
      }
    },
    searchQuery() {
      this.images = []
      this.searchPage = 1
      this.hasNextPage = null
      this.zoomMode = false

      this.search()
    },
  },
}
</script>

<style>
  .-z-1 { z-index: -1 !important; }
  .lazyloaded { transition: all 0.3s ease; }
</style>
