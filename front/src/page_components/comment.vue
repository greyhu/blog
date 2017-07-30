<template>
  <div>
    <div style="padding: 8px 0;" :style="{paddingBottom:line?'8px':'0'}">
      <div style="display: flex;justify-content: space-between">
        <span>
          <span v-if="comment.user.id == 1" style="background-color: orange;padding: 2px 4px;border-radius: 4px;">博主</span>
          <span v-if="user && comment.user.id == user.id" style="background-color: lightgray;padding: 2px 4px;margin-right: 2px;border-radius: 4px;">自己</span>

          {{comment.user.name}}
            ：<span v-if="comment.private" style="background-color: mediumpurple;padding: 2px 4px;border-radius: 4px;">私聊</span>
        </span>
        <span style="color: lightslategray;">
          <span v-if="user && user.isadmin" style="margin-right: 8px;text-decoration: underline;cursor: pointer;" @click="showReply=!showReply">回复</span>
          <span v-if="user && user.isadmin" style="margin-right: 8px;text-decoration: underline;cursor: pointer;color:red;" @click="delReply">删除</span>
          {{comment.updated_at.toString().replace(/:\d+/,'')}}
        </span>
      </div>
      <div>
        {{comment.content}}
      </div>
      <div v-if="comment.reply" style="margin-left: 40px;">
        <div style="height: 1px;background-color: lightgray;margin: 8px 0;"></div>
        <span v-if="comment.user.id == 1" style="background-color: orange;padding: 2px 4px;border-radius: 4px;">博主回复</span>
        <span v-if="comment.reply_private" style="background-color: mediumpurple;padding: 2px 4px;border-radius: 4px;">私聊</span>
        {{comment.reply}}
      </div>
      <div v-if="showReply" style="display: flex;align-items: center;margin-top: 6px;">
        <i-input placeholder="写下或修改你的回复..." v-model="replyTxt"></i-input>
        <div style="display: flex;align-items: center;">
          <i-switch size="large" v-model="replyPrivate" style="margin-left: 8px;">
            <span slot="open">私聊</span>
            <span slot="close">公开</span>
          </i-switch>
          <Button type="primary" @click="reply" style="margin-left: 8px;">回复</Button>
        </div>
      </div>
    </div>
    <div v-if="line" style="background-color: lightgray;height: 1px;"></div>
  </div>
</template>

<script>
  export default {
    props: {
      comment: Object,
      line: false
    },
    data () {
      return {
        showReply: false,
        replyTxt: '',
        replyPrivate: false
      }
    },
    computed: {
      ...require('vuex').mapState([
        'user'
      ])
    },
    methods: {
      reply () {
        if (this.replyTxt.length < 1) return
        this.$store.dispatch('HTTP_AUTH_POST', {
          url: `/api/comments/${this.comment.id}/reply`,
          data: {
            reply: this.replyTxt,
            reply_private: this.replyPrivate
          }
        }).then(json => {
          this.comment.reply = this.replyTxt
          this.comment.reply_private = this.replyPrivate
          this.replyTxt = ''
          this.showReply = false
        }).catch(err => {
          this.$Message.error(err)
        })
      },
      delReply () {
        this.$Modal.confirm({
          title: '请确认',
          content: `你确定要删除此回复么？`,
          onOk: () => {
            this.$store.dispatch('HTTP_AUTH_DELETE', {
              url: `/api/comments/${this.comment.id}`
            }).then(json => {
              this.$Message.success('删除成功')
              this.$emit('del')
            }).catch(err => {
              this.$Message.error(err)
            })
          }
        })
      }
    }
  }
</script>
