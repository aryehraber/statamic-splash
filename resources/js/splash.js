import 'lazysizes'
import Splash from './components/Splash.vue'
import { setupFieldtypeInstance } from './composables/fieldtype'

// Register the component with Statamic for Vue 3
Statamic.$components.register('splash-fieldtype', {
  ...Splash,
  // Set up the fieldtype instance for the composable
  created() {
    setupFieldtypeInstance(this)
  }
})
