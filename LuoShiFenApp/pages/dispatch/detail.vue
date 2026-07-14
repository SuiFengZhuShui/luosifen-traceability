<template>
  <view class="page">
    <view class="card" v-if="qrcodeUrl">
      <text class="title">发货成功</text>
      <image :src="qrcodeUrl" mode="widthFix" class="qrcode" @click="preview" />
      <text class="hint">扫描二维码签收</text>
    </view>
    <view class="card" v-else>
      <text class="title">发货成功</text>
      <text class="hint">二维码暂未生成</text>
    </view>
  </view>
</template>

<script>
export default {
  data() { return { qrcodeUrl: '' }; },
  onLoad(options) { this.qrcodeUrl = decodeURIComponent(options.qrcode_url || ''); },
  methods: { preview() { uni.previewImage({ urls: [this.qrcodeUrl] }); } }
};
</script>

<style scoped>
.page { background: linear-gradient(135deg, #007aff, #5673ff); min-height: 100vh; padding: 40rpx; }
.card { background: #fff; border-radius: 30rpx; padding: 60rpx 40rpx; text-align: center; }
.title { font-size: 36rpx; font-weight: bold; }
.qrcode { width: 400rpx; margin: 40rpx auto; border-radius: 20rpx; }
.hint { color: #888; font-size: 28rpx; margin-top: 20rpx; }
</style>