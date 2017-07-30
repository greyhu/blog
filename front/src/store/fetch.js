import axios from 'axios'
axios.defaults.baseURL = ''
// axios.defaults.baseURL = 'http://127.0.0.1:8000'
import Vue from 'vue'
Vue.prototype.$http = axios

export function fetch (state, path, method, data, options) {
  if (typeof method === 'object') {
    data = method
    method = 'GET'
  }
  if (!method) method = 'GET'
  method = method.toUpperCase()

  return new Promise((resolve, reject) => {
    let axiosOptions = {
      method: method,
      responseType: 'json',
      // withCredentials: true,
      data: data,
      url: path
    }
    if (method === 'GET') {
      delete axiosOptions.data
      axiosOptions.params = data
    }
    let absolute = /^http(s?):\/\//.test(path)
    if (absolute) {
      delete axiosOptions.baseURL
    }
    options && Object.assign(axiosOptions, options)

    axios(axiosOptions).then(response => {
      // console.log(response)
      if (response.status === 200) {
        let json = response.data
        // console.log('response=', JSON.stringify(response))
        if (typeof json.stat !== 'number' || json.stat === 0) {
          resolve(json)
        } else {
          reject(json.msg)
        }
      } else {
        // console.log(response)
        reject(response.statusText)
      }
    })
    .catch(err => {
      if (err.response) {
        reject(`网络错误：${err.response.status} - ${err.response.statusText}`)
        return
      }
      reject(err.message)
    })
  })
}
