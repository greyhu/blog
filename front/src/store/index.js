import Vue from 'vue'
import Vuex from 'vuex'
import * as actions from './actions'
import * as mutations from './mutations'
Vue.use(Vuex)

const store = new Vuex.Store({
  actions,
  mutations,
  state: {
    apiClient: {
      id: 2,
      secret: 'cogNQ1cjjnvSAQcBJPpeu3PKQd9A8Hs7Sw5RmaUz'
    },
    logined: false,
    layer: {
      login: false
    },
    user: null,
    // {
    //   nick: '',
    //   isadmin: false,
    //   id: 0
    // },
    articleids: [],
    articles: {}, // hash
    catalogs: [],
    tags: [],
    tokendata: {}
  }
})

export default store
