import { computed } from 'vue'

// Fieldtype composable for Vue 3 compatibility
// This replaces the Vue 2 Fieldtype mixin
export function useFieldtype() {
  // Get the fieldtype instance from the global Statamic object
  // This will be set up by the main splash.js file
  const fieldtypeInstance = window.__STATAMIC_SPLASH_FIELDTYPE__ || {}
  
  // Return reactive computed properties
  return {
    meta: computed(() => fieldtypeInstance.meta || {}),
    config: computed(() => fieldtypeInstance.config || {}),
    value: computed(() => fieldtypeInstance.value || null),
  }
}

// Helper to set up the fieldtype instance
export function setupFieldtypeInstance(component) {
  window.__STATAMIC_SPLASH_FIELDTYPE__ = component
}
