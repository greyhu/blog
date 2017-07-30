// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import iView from 'iview'
import 'iview/dist/styles/iview.css' // 使用 CSS
// import 'assets/css/blog.scss'
import VueHighlightJS from 'vue-highlightjs'

Vue.use(iView)
Vue.use(VueHighlightJS)
Vue.config.productionTip = false

import store from './store'
/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: { App }
})
