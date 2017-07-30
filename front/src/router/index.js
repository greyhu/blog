import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'index',
      component: require('../pages/index.vue')
    },
    {
      path: '/catalog/:catalogid',
      name: 'catalog',
      component: require('../pages/index.vue')
    },
    {
      path: '/tag/:tagid',
      name: 'tag',
      component: require('../pages/index.vue')
    }
  ]
})
