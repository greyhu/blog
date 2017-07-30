<template>
  <Card :bordered="false" class="article margin-bottom" style="width:100%;">
    <div slot="title">
      <p v-html="article.title"></p>
      <div style="color:lightslategray;">
        <a :href="`#/catalog/${article.catalog.id}`" target="_blank" style="text-decoration: underline;">{{article.catalog?article.catalog.name:'未分类'}}</a>
        ⋅ {{article.created_at}}
        <span v-if="article.tags.length>0">
          ⋅
          <span v-for="tag in article.tags" :key="tag.id">
            &nbsp;<a :href="`#/tag/${tag.id}`" target="_blank" style="text-decoration: underline;">{{tag.name }}</a>
          </span>
        </span>
      </div>
    </div>
    <a href="#" slot="extra">
      <span v-if="user && user.isadmin" class="float-right" style="font-size: 18px;">
        <span v-on:click="editArticle" class="text-primary btn_edit_article" style="cursor: pointer;">
          <i class="iconfont icon-edit"></i>
        </span>
        &nbsp;<span v-on:click="delArticle" class="text-danger" style="cursor: pointer;">
          <i class="iconfont icon-delete"></i>
        </span>
      </span>
    </a>
    <!--<div style="position:relative;">-->
    <div v-html="article.content" v-highlightjs style="overflow: hidden;" :style="{maxHeight:fold?'486px':''}" ref="divContent"></div>
    <Card v-if="!fold && comments" :padding="10">
      <div style="display: flex;align-items: center;margin-bottom: 6px;">
        <i-input placeholder="请写下你的评论..." v-model="newcomment" @on-focus="!user && $store.commit('SHOW_LAYER', 'login')"></i-input>
        <div v-if="showCommentBtn" style="display: flex;align-items: center;margin-left: 8px;">
          <i-switch size="large" v-model="private">
            <span slot="open">私聊</span>
            <span slot="close">公开</span>
          </i-switch>
          <Button type="primary" @click="addComment" style="margin-left: 8px;">发表评论</Button>
        </div>
      </div>
        <Comment v-for="(comment,i) in comments" :key="comment.id" :comment="comment" :line="i<comments.length-1" @del="comments.splice(index,1)">
        </Comment>
    </Card>
    <div style="height: 20px;"></div>

    <div v-if="fold" style="position: absolute;bottom: 0;margin: 0 -16px;background-color: rgba(184, 248, 184, 0.9);width: 100%;height:32px;text-align: center;border-radius: 0 0 4px 4px;line-height: 32px;" @click="showComments">展开{{shortContent?'':'全文和'}}评论</div>
    <div v-else style="position: absolute;bottom: 0;margin: 0 -16px;background-color: rgba(184, 248, 184, 0.9);width: 100%;height:32px;text-align: center;border-radius: 0 0 4px 4px;line-height: 32px;" @click="fold=true">收起{{shortContent?'':'全文和'}}评论</div>
  </Card>
</template>

<script>
  export default {
    components: {
      Comment: require('../page_components/comment.vue')
    },
    props: {
      article: Object,
      index: {
        Number,
        default: 0
      }
    },
    data () {
      return {
        fold: true,
        bmounted: false,
        comments: null,
        newcomment: '',
        private: false
      }
    },
    computed: {
      showCommentBtn () {
        return this.user && this.newcomment.length > 0
      },
      shortContent () {
        if (!this.bmounted || !this.$refs) return false
//        console.log(this.$refs.divContent.offsetHeight)
        return this.$refs.divContent.offsetHeight < 486
      },
      ...require('vuex').mapState([
        'user'
      ])
    },
    methods: {
      addComment () {
        if (this.newcomment.length < 1) return
        this.$store.dispatch('HTTP_AUTH_POST', {
          url: `/api/articles/${this.article.id}/comments`,
          data: {
            content: this.newcomment,
            private: this.private
          }
        }).then(json => {
          this.newcomment = ''
          this.comments.push(json.comment)
        }).catch(err => {
          this.$Message.error(err)
        })
      },
      showComments () {
        if (this.comments) {
          this.fold = false
          return
        }

        this.$store.dispatch(this.user && this.user.isadmin ? 'HTTP_AUTH_GET' : 'HTTP_GET', {
          url: `/api/articles/${this.article.id}/${this.user && this.user.isadmin ? 'allcomments' : 'comments'}`
        }).then(json => {
          this.fold = false
          this.comments = json.comments
        }).catch(err => {
          this.$Message.error(err)
        })
      },
      editArticle () {
        this.$emit('edit')
      },
      delArticle () {
        this.$Modal.confirm({
          title: '请确认',
          content: `你确定要删除《${this.article.title}》么？`,
          onOk: () => {
            this.$store.dispatch('HTTP_AUTH_DELETE', {
              url: `/api/articles/${this.article.id}`
            }).then(json => {
              this.$Message.success('删除成功')
              this.$store.commit('DELETE_ARTICLE_ID', this.index)
              this.$emit('del')
            }).catch(err => {
              this.$Message.error(err)
            })
          }
        })
      }
    },
    mounted () {
      this.bmounted = true
    }
  }
</script>

<style>
  .article .ivu-card-head {
    background-color: #fdfdfd;
    border-radius: 4px 4px 0 0;
  }
</style>
