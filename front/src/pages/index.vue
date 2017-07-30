<template>
<div>
  <div class="container-main" style="display: flex;flex-direction: row;justify-content: space-between;">
    <div v-cloak>
      <div style="display: flex;">
        <img :src="settings.logopath" height="50" />
        &nbsp;
        <span style="font-size: 24px;color: #464c5b;font-weight: bold;">{{settings.title}}</span>
      </div>
      <div>{{settings.desc}}</div>
    </div>
    <div style="margin-top: 8px;">
      <Dropdown v-if="user" @on-click="UserMenuClick" placement="bottom-end">
        <a href="javascript:void(0)">
          <i class="iconfont icon-my"></i> {{ user.nick }}
          <Icon type="arrow-down-b"></Icon>
        </a>
        <Dropdown-menu slot="list">
          <Dropdown-item name="logout">
            <i class="iconfont icon-exit"></i> 登出
          </Dropdown-item>
          <Dropdown-item v-if="user && user.isadmin" name="settings">
            <i class="iconfont icon-cascades"></i> 设置
          </Dropdown-item>
          <Dropdown-item v-if="user && user.isadmin" name="add">
            <i class="iconfont icon-write"></i> 撰写文章
          </Dropdown-item>
        </Dropdown-menu>
      </Dropdown>
      <span v-else @click="$store.commit('SHOW_LAYER', 'login')" style="cursor: pointer;">登录</span>
    </div>
  </div>
  <div style="height: 1px;" class="bg-color-gray margin-bottom">
  </div>
  <div v-cloak class="container-main" style="padding-bottom: 10px;">
    <Row :gutter="10">
      <i-col :sm="18" :lg="20">
        <DIV_Article v-for="(article,index) in page_articles" :key="article.id" :article="article" :index="index" @edit="editArticle(article,index)" @del="delArticle(article,index)">
        </DIV_Article>
        <div style="text-align: center;">
          <Page :total="articleids.length" :current="page_current" show-total :page-size="page_size" @on-change="onPageChange" style="display: inline-block;"></Page>
        </div>
      </i-col>
      <i-col :sm="6" :lg="4">
        <Card class="margin-bottom" :bordered="false">
          <p slot="title">
            分类
          </p>
          <ul>
            <li v-for="catalog in catalogs" :key="catalog.name" v-if="catalog.articles_count>0">
              <a v-if="catalog.id!=$route.params.catalogid" :href="`#/catalog/${catalog.id}`" target="_blank" style="text-decoration: underline;">{{catalog.name}}</a>
              <span v-else>{{catalog.name}}</span>
              ({{catalog.articles_count}})
            </li>
          </ul>
        </Card>
        <Card class="margin-bottom" :bordered="false">
          <p slot="title">
            热门标签
          </p>
          <ul>
            <li v-for="tag in tags" :key="tag.name"  v-if="tag.articles_count>0">
              <a v-if="tag.id!=$route.params.tagid" :href="`#/tag/${tag.id}`" target="_blank" style="text-decoration: underline;">{{tag.name}}</a>
              <span v-else>{{tag.name}}</span>
              ({{tag.articles_count}})
              </li>
          </ul>
        </Card>
      </i-col>
    </Row>
  </div>
  <Modal v-model="layer.login" style="font-size: 14px;">
    <Tabs v-model="login.tab" ref="loginTab" @on-click="changeLoginTab">
      <Tab-pane label="登陆" name="login">
        <div style="font-size: 14px;">邮箱</div>
        <Input v-model="login.email" type="text" />
        <div style="margin-top: 8px;font-size: 14px;">密码</div>
        <Input type="password" v-model="login.password" @on-enter="doLogin"/>
      </Tab-pane>
      <Tab-pane label="注册" name="reg">
        <i-input v-model="login.email" placeholder="找回密码会用" type="text">
          <span slot="prepend">邮 箱</span>
        </i-input>
        <i-input style="margin-top: 8px;" type="password" placeholder="至少6位" v-model="login.password">
        <span slot="prepend">密 码</span>
        </i-input>
        <i-input style="margin-top: 8px;" v-model="login.name" placeholder="展示用" type="text">
          <span slot="prepend">昵 称</span>
        </i-input>
      </Tab-pane>
      <!--<Tab-pane label="找回密码" name="reset">-->
        <!--<div style="font-size: 14px;">邮箱</div>-->
        <!--<Input v-model="login.email" type="text" />-->
      <!--</Tab-pane>-->
    </Tabs>

    <div slot="footer">
      <Alert type="error" show-icon v-if="login.errormsg">{{login.errormsg}}</Alert>
      <Button v-if="login.tab === 'login'" type="warning" long size="large" @click="doLogin">登录</Button>
      <Button v-else-if="login.tab === 'reg'" type="success" long size="large" @click="doReg">注册</Button>
      <Button v-else="login.tab === 'reset'" type="error" long size="large" @click="doReset">发送重置邮件</Button>
    </div>
  </Modal>
  <Modal v-model="editor.show" :title="editor.new?'添加文章':'修改文章'" :ok-text="editor.new?'添加':'修改'" class="modal-editor" @on-ok="editorSave">
    <Row style="margin-bottom: 8px;">
      <i-col :xs="6" :sm="3">标题:</i-col>
      <i-col :xs="18" :sm="21">
      <Input v-model="editor.article.title"/>
      </i-col>
    </Row>
    <Row style="margin-bottom: 8px;">
      <i-col :xs="6" :sm="3">分类:</i-col>
      <i-col :xs="18" :sm="21">
      <Input v-model="editor.article.catalog.name"/>
      </i-col>
    </Row>
    <Row style="margin-bottom: 8px;">
      <i-col :xs="6" :sm="3">标签:</i-col>
      <i-col :xs="18" :sm="21">
      <Input v-model="editor.article.str_tags" placeholder="（空格分隔）"/>
      </i-col>
    </Row>
    <div>
      <div>图片上传：（上传后可点击图片复制地址）（也可以直接拖图片到编辑器）</div>
      <div style="display: flex;flex-wrap: wrap;align-items: center;">
        <uploadedImg v-for="img in imgs" :key="img" :img="img">
        </uploadedImg>
        <Upload accept="image/*" style="margin-bottom: 8px;" type="drag" name="upload" :action="`${baseURL}/api/upload`" :headers="{'Authorization': `Bearer ${$store.state.tokendata.access_token}`}" :on-success="uploadSuccess" :show-upload-list="false">
          <div style="padding: 10px 0">
            <Icon type="ios-cloud-upload" size="52" style="color: #3399ff"></Icon>
            <p>点击或将文件拖拽到这里上传</p>
          </div>
        </Upload>
      </div>

    </div>
    <ckeditor v-model="editor.article.content" :config="editor.config"></ckeditor>
  </Modal>

  <Modal v-model="settings.show" @on-ok="saveSettings" title="修改设置">
    <img :src="settings.logopath" height="90" />
    <Upload accept="image/*" style="display: inline-block;" type="drag" :action="`${baseURL}/api/saveLogo`" :headers="{'Authorization': `Bearer ${$store.state.tokendata.access_token}`}" :on-success="uploadLogoSuccess" :show-upload-list="false">
      <div style="padding: 10px 0">
        <Icon type="ios-cloud-upload" size="52" style="color: #3399ff"></Icon>
        <p>点击或将文件拖拽到这里上传</p>
      </div>
    </Upload>
    <div style="background-color: lightgray;height: 1px;margin: 12px 0;"></div>
    博客标题 <Input v-model="settingsEdit.title"/>
    <div style="margin-top: 8px;">描述</div>
    <Input v-model="settingsEdit.desc"/>
  </Modal>
</div>
</template>

<script>
  export default {
    components: {
      DIV_Article: require('../page_components/article.vue'),
      Ckeditor: require('../components/ckeditor.vue'),
      uploadedImg: require('../page_components/uploadedImg.vue')
    },
    data () {
      return {
        login: {
          tab: 'login',
          email: '',
          password: '',
          name: '',
          errormsg: ''
        },
        settings: {
          show: false,
          title: '',
          dessc: '',
          logopath: '/storage/logo.jpg'
        },
        settingsEdit: {
        },
        editor: {
          show: false,
          new: true,
          index: 0,
          article: {
            id: 0,
            title: '',
            catalog: {
              name: ''
            },
            str_tags: '',
            content: ''
          },
          config: {
            extraPlugins: 'uploadimage',
            uploadUrl: `${this.$http.defaults.baseURL}/api/upload`,
            imageUploadUrl: `${this.$http.defaults.baseURL}/api/upload`
//            filebrowserUploadUrl: `${this.$http.defaults.baseURL}/api/upload`
          }
        },
        imgs: [],
        page_current: 1,
        page_all: 1,
        page_size: 5,
        page_articles: []
      }
    },
    computed: {
      baseURL () {
        return this.$http.defaults.baseURL
      },
      ...require('vuex').mapState([
        'user', 'articleids', 'articles', 'catalogs', 'tags', 'layer', 'apiClient'
      ])
    },
    methods: {
      changeLoginTab (name) {
        this.login.tab = name
      },
      onPageChange (page) {
        this.page_current = page
        this.page_articles = []
        let start = (this.page_current - 1) * this.page_size
        let ids = this.articleids.slice(start, start + this.page_size)
        let idstoload = ids.filter(id => {
          return !this.articles[id]
        })
        if (idstoload.length < 1) {
          this.page_articles = ids.map(id => {
            return this.articles[id]
          })
          return
        }
        this.$store.dispatch('HTTP_GET', {
          url: '/api/getArticles',
          data: {
            ids: idstoload.join(',')
          }
        }).then(json => {
          json.articles.forEach(article => {
            this.articles[article.id] = article
          })
          this.page_articles = ids.map(id => {
            return this.articles[id]
          })
        })
      },
      doLogin () {
        this.login.errormsg = ''
        this.$store.dispatch('HTTP_POST', {
          url: '/oauth/token',
          data: {
            grant_type: 'password',
            client_id: this.apiClient.id,
            client_secret: this.apiClient.secret,
            username: this.login.email,
            password: this.login.password,
            scope: ''
          }
        }).then(json => {
          this.$store.commit('SET_TOKEN', json)
          this.getUser()
        }).catch(err => {
          this.login.errormsg = err
        })
      },
      doReg () {
        this.login.errormsg = ''
        this.$store.dispatch('HTTP_POST', {
          url: '/api/reg',
          data: {
            name: this.login.name,
            email: this.login.email,
            password: this.login.password
          }
        }).then(json => {
          this.$Message.success('注册成功，正在自动登录...')
          this.login.tab = 'login'
          this.doLogin()
        }).catch(err => {
          this.login.errormsg = err
        })
      },
      doReset () {
        this.login.errormsg = ''
        this.$store.dispatch('HTTP_POST', {url: '/api/resetpwd'})
      },
      getUser () {
        this.$store.dispatch('HTTP_AUTH_GET', {
          url: '/api/user'
        }).then(json => {
          this.$store.commit('SET_USER', json.user)
          this.$store.commit('HIDE_LAYER', 'login')
          this.$Message.success('登陆成功！')
        }).catch(err => {
          console.log(err)
          this.$store.commit('SET_USER', null)
        })
      },
      addArticle () {
        this.editor.new = true
        this.editor.show = true
        this.editor.article = {
          id: 0,
          title: '',
          catalog: {
            name: ''
          },
          str_tags: '',
          content: ''
        }
      },
      delArticle (article, index) {
        this.onPageChange(this.page_current)
      },
      editArticle (article, index) {
        this.editor.new = false
        this.editor.index = index
        this.editor.show = true
        this.editor.article = Object.assign({
          str_tags: article.tags.map(tag => {
            return tag.name
          }).join(' ')
        }, article)
      },
      editorSave () {
        let data = {
          title: this.editor.article.title,
          catalog: this.editor.article.catalog.name,
          tags: this.editor.article.str_tags,
          content: this.editor.article.content
        }
        if (this.editor.new) {
          this.$store.dispatch('HTTP_AUTH_POST', {url: `/api/articles`, data: data})
          .then(json => {
            this.$Message.success('添加成功')
            this.articles[json.article.id] = json.article
            this.articleids.unshift(json.article.id)
            this.onPageChange(this.page_current)
          }).catch(err => {
            this.$Message.error(err)
          })
          return
        }

        this.$store.dispatch('HTTP_AUTH_PUT', {url: `/api/articles/${this.editor.article.id}`, data: data})
        .then(json => {
          this.$Message.success('修改成功')
          this.$set(this.articles, this.editor.article.id, json.article)
          this.$set(this.page_articles, this.editor.index, json.article)
        }).catch(err => {
          this.$Message.error(err)
        })
        return
      },
      UserMenuClick (name) {
        if (name === 'logout') {
          this.logout()
          return
        }
        if (name === 'add') {
          this.addArticle()
          return
        }
        if (name === 'settings') {
          this.settingsEdit.title = this.settings.title
          this.settingsEdit.desc = this.settings.desc
          this.settings.show = true
          return
        }
      },
      logout () {
        this.$store.commit('SET_USER', null)
        this.$store.commit('SET_TOKEN', {})
      },
      uploadLogoSuccess (response, file, fileList) {
        if (response.stat === 0) {
          this.$Message.success('上传成功')
          this.settings.logopath = `${response.path}?${new Date().getTime()}`
        } else {
          this.$Message.error(response.msg || response.stat)
        }
//        console.log(response, file, fileList)
      },
      saveSettings () {
        this.$store.dispatch('HTTP_AUTH_PUT', {url: '/api/settings', data: this.settingsEdit})
        .then(json => {
          this.settings.title = this.settingsEdit.title
          this.settings.desc = this.settingsEdit.desc
          this.$Message.success('修改成功')
        })
        .catch(err => {
          this.$Notice.error({title: err})
        })
      },
      uploadSuccess (response, file, fileList) {
        this.imgs.push(response.url)
      }
    },
    created () {
      this.$store.dispatch('BLOG_INIT', {catalog: this.$route.params.catalogid, tag: this.$route.params.tagid})
      .then(json => {
        this.page_current = 1
        this.settings.title = json.settings.title
        this.settings.desc = json.settings.desc
        this.settings.logopath = json.logopath
        this.onPageChange(1)
      })
      .catch(err => {
        this.$Notice.error({title: err})
      })
      this.$store.commit('SET_TOKEN', JSON.parse(localStorage.getItem('token') || '{}'))
      this.getUser()
    }
  }
</script>

<style >
  .modal-editor .ivu-modal {
    width: 90% !important;
  }
  .ivu-modal-close {
    z-index: 2;
  }
  .hljs {
    word-break: break-all;
    white-space: pre-wrap;
  }
</style>
