import 'lazysizes'
import Splash from './components/Splash.vue'

Statamic.booting(() => {
    Statamic.$components.register('splash-fieldtype', Splash)
})
