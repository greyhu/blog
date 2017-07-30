<template>
  <div style="margin-right: 8px;text-align: center;margin-bottom: 8px;">
    <div style="min-height: 70px;display: flex;align-items: center;justify-content: center;cursor: pointer;" ref="copybtn">
      <img style="max-height: 70px;min-height: 20px;" :src="img" />
    </div>
    <i-input :value="img" style="width: 100px;" size="small"></i-input>
  </div>
</template>

<script>
  import Clipboard from 'clipboard'
  export default {
    props: {
      img: String
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
    },
    mounted () {
      let clipboard = new Clipboard(this.$refs.copybtn, {
        text: () => {
          return this.img
        }
      })
      clipboard.on('success', e => {
        this.$Message.success('复制地址成功')
      })
      clipboard.on('error', e => {
        this.$Message.success('复制地址失败')
      })
    }
  }
</script>
