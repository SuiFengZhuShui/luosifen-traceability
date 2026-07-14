<template>
    <view class="page">
        <view class="card">
            <text class="title">发货成功</text>
            <image v-if="qrcodeUrl" :src="qrcodeUrl" mode="widthFix" class="qrcode" @click="preview" />
            <text class="hint">请将二维码打印并贴在货物上</text>
        </view>
    </view>
</template>

<script>
export default {
    data() { return { qrcodeUrl: '' }; },
    onLoad(options) {
        this.qrcodeUrl = options.qrcode_url || '';
    },
    methods: {
        preview() {
            uni.previewImage({ urls: [this.qrcodeUrl] });
        }
    }
};
</script>

<style scoped>
.page { background: #f0f4ff; min-height: 100vh; padding: 40rpx; }
.card { background: white; border-radius: 30rpx; padding: 60rpx 40rpx; text-align: center; }
.title { font-size: 36rpx; font-weight: bold; }
.qrcode { width: 400rpx; margin: 40rpx auto; border-radius: 20rpx; }
.hint { color: #888; font-size: 28rpx; }
</style>