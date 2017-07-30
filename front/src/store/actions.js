import { fetch } from './fetch.js'

export function HTTP_REQUEST ({ commit, state }, data) {
  return fetch(state, data.url, data.method, data.data, data.options)
}
export function HTTP_GET ({ commit, state }, data) {
  return fetch(state, data.url, 'GET', data.data, data.options)
}
export function HTTP_POST ({ commit, state }, data) {
  return fetch(state, data.url, 'POST', data.data, data.options)
}
export function HTTP_PUT ({ commit, state }, data) {
  return fetch(state, data.url, 'PUT', data.data, data.options)
}
export function HTTP_DELETE ({ commit, state }, data) {
  return fetch(state, data.url, 'DELETE', data.data, data.options)
}

export function HTTP_AUTH_REQUEST ({ commit, state }, data) {
  return fetch(state, data.url, data.method, data.data, Object.assign({headers: {'Authorization': `Bearer ${state.tokendata.access_token}`}}, data.options))
}
export function HTTP_AUTH_GET ({ commit, state }, data) {
  return fetch(state, data.url, 'GET', data.data, Object.assign({headers: {'Authorization': `Bearer ${state.tokendata.access_token}`}}, data.options))
}
export function HTTP_AUTH_POST ({ commit, state }, data) {
  return fetch(state, data.url, 'POST', data.data, Object.assign({headers: {'Authorization': `Bearer ${state.tokendata.access_token}`}}, data.options))
}
export function HTTP_AUTH_PUT ({ commit, state }, data) {
  return fetch(state, data.url, 'PUT', data.data, Object.assign({headers: {'Authorization': `Bearer ${state.tokendata.access_token}`}}, data.options))
}
export function HTTP_AUTH_DELETE ({ commit, state }, data) {
  return fetch(state, data.url, 'DELETE', data.data, Object.assign({headers: {'Authorization': `Bearer ${state.tokendata.access_token}`}}, data.options))
}

export function BLOG_INIT ({ commit, state }, params) {
  return fetch(state, '/api/index', 'GET', params)
  .then(json => {
    commit('SET_ARTICLE_IDS', json.articleids)
    commit('SET_CATALOGS', json.catalogs)
    commit('SET_TAGS', json.tags)
    commit('SET_API_CLIENT', json.settings.apiclient)
    return json
  })
}
