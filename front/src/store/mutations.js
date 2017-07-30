export function SET_ARTICLE_IDS (state, v) {
  state.articleids = v
}

export function SET_CATALOGS (state, v) {
  state.catalogs = v
}

export function SET_TAGS (state, v) {
  state.tags = v
}

export function SET_TOKEN (state, v) {
  state.tokendata = v
  if (v && v.access_token) {
    localStorage.setItem('token', JSON.stringify(v))
  } else {
    localStorage.removeItem('token')
  }
}

export function SET_USER (state, v) {
  state.user = v
}

export function DELETE_ARTICLE_ID (state, index) {
  state.articleids.splice(index, 1)
}

export function SHOW_LAYER (state, layer) {
  state.layer[layer] = true
}

export function HIDE_LAYER (state, layer) {
  state.layer[layer] = false
}
