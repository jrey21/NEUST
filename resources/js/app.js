import './bootstrap'; 
import '../css/app.css';

import { createApp, h } from 'vue'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Layout from './Layouts/Layout.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faEye, faEyeSlash } from '@fortawesome/free-solid-svg-icons';

library.add(faEye, faEyeSlash);

createInertiaApp({
  title: (title) => `NGLAS ${title}`,
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
   let page = pages[`./Pages/${name}.vue`]
   page.default.layout = page.default.layout || Layout;
   return page;

   
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .component('Head',Head)
      .component('Link',Link)
      .component('FontAwesomeIcon', FontAwesomeIcon)
      .mount(el)

  },
  progress: {
  
    // The color of the progress bar...
    color: '#29d',
    includeCSS: true,
    showSpinner: false,
  },
})

