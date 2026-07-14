<template>
  <view class="page">
    <view class="header">
      <view class="user">
        <image class="avatar" src="/static/default-avatar.png" mode="aspectFill" />
        <text class="name">{{ userName }}</text>
      </view>
      <text class="logout-text" @click="logout">退出</text>
    </view>

    <view class="stats">
      <view class="stat">
        <text class="num">{{ records.length }}</text>
        <text class="label">全部发货</text>
      </view>
      <view class="stat">
        <text class="num">{{ pendingCount }}</text>
        <text class="label">待签收</text>
      </view>
    </view>

    <view class="card">
      <view class="card-title">最近发货记录</view>
      <view v-if="records.length === 0" class="empty">暂无记录</view>
      <view v-for="(item, index) in records" :key="index" class="record">
        <view class="record-header">
          <text class="goods">{{ item.product_name || '未命名' }}</text>
          <text :class="item.status === 'signed' ? 'tag signed' : 'tag pending'">
            {{ item.status === 'signed' ? '已签收' : '待签收' }}
          </text>
        </view>
        <view class="info">批次号：{{ item.batch_no }}</view>
        <view class="info">数量：{{ item.quantity }}</view>
        <view class="info">收货单位：{{ item.receiving_unit || '未指定' }}</view>

        <!-- 二维码展示（仅待签收） -->
        <view v-if="item.status === 'pending' && item.qrcode_url" class="qrcode-section">
          <!-- show-menu-by-longpress 允许长按保存图片 -->
          <image 
            :src="item.qrcode_url" 
            mode="widthFix" 
            class="qrcode-img" 
            @click="previewQrcode(item.qrcode_url)"
            show-menu-by-longpress
          />
          <text class="qrcode-hint">长按二维码可保存并打印</text>
          <view class="qrcode-actions">
            <button class="action-btn-small" @click="saveQrcode(item.qrcode_url)">保存图片</button>
            <button class="action-btn-small print-btn" @click="previewQrcode(item.qrcode_url)">放大查看</button>
          </view>
        </view>
      </view>
    </view>

    <!-- 打印提示弹窗 -->
    <view v-if="showPrintTip" class="print-tip-overlay" @click="showPrintTip = false">
      <view class="print-tip-card" @click.stop>
        <text class="tip-title">如何打印二维码</text>
        <view class="tip-content">
          <text class="tip-step">1. 长按二维码 → 保存图片到手机</text>
          <text class="tip-step">2. 打开微信 → 文件传输助手</text>
          <text class="tip-step">3. 发送图片 → 电脑端微信接收</text>
          <text class="tip-step">4. 电脑端打印图片</text>
        </view>
        <button class="tip-close-btn" @click="showPrintTip = false">知道了</button>
      </view>
    </view>
  </view>
</template>

<script>
import { getDispatchRecords } from '@/api/index.js';
export default {
  data() { 
    return { 
      records: [],
      showPrintTip: false
    }; 
  },
  computed: {
    userName() { return uni.getStorageSync('userName') || '发货员'; },
    pendingCount() { return this.records.filter(r => r.status === 'pending').length; }
  },
  onShow() {
    if (!uni.getStorageSync('token')) { uni.reLaunch({ url: '/pages/login/login' }); return; }
    this.load();
  },
  methods: {
    async load() {
      try {
        const res = await getDispatchRecords();
        this.records = Array.isArray(res) ? res : [];
      } catch (e) { this.records = []; }
    },

    // 预览二维码
    previewQrcode(url) {
      if (!url) return;
      uni.previewImage({ urls: [url] });
    },

    // 保存二维码到相册
    saveQrcode(url) {
      if (!url) return;
      
      // 先下载图片到本地
      uni.downloadFile({
        url: url,
        success: (res) => {
          if (res.statusCode === 200) {
            // 保存到相册
            uni.saveImageToPhotosAlbum({
              filePath: res.tempFilePath,
              success: () => {
                uni.showToast({ title: '已保存到相册', icon: 'success' });
                // 显示打印提示
                this.showPrintTip = true;
              },
              fail: () => {
                uni.showToast({ title: '保存失败，请授权相册权限', icon: 'none' });
              }
            });
          }
        },
        fail: () => {
          uni.showToast({ title: '下载失败', icon: 'none' });
        }
      });
    },

    logout() {
      uni.removeStorageSync('token');
      uni.removeStorageSync('userName');
      uni.reLaunch({ url: '/pages/login/login' });
    }
  }
};
</script>

<style scoped>
.page { min-height: 100vh; background: linear-gradient(135deg, #007aff, #5673ff); padding: 30rpx; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40rpx; }
.user { display: flex; align-items: center; }
.avatar { width: 70rpx; height: 70rpx; border-radius: 50%; background: #fff; margin-right: 20rpx; }
.name { color: #fff; font-size: 34rpx; font-weight: bold; }
.logout-text { color: #fff; font-size: 26rpx; padding: 8rpx 20rpx; background: rgba(255,255,255,0.2); border-radius: 30rpx; }
.stats { display: flex; gap: 20rpx; margin-bottom: 40rpx; }
.stat { flex:1; background: rgba(255,255,255,0.2); border-radius: 20rpx; padding: 25rpx; text-align: center; }
.num { color: #fff; font-size: 48rpx; font-weight: bold; }
.label { color: rgba(255,255,255,0.9); font-size: 24rpx; display: block; margin-top: 10rpx; }
.card { background: #fff; border-radius: 30rpx; padding: 30rpx; }
.card-title { font-size: 32rpx; font-weight: bold; margin-bottom: 25rpx; }
.record { padding: 25rpx 0; border-bottom: 1rpx solid #f0f0f0; }
.record:last-child { border-bottom: none; }
.record-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10rpx; }
.goods { font-size: 30rpx; font-weight: bold; color: #333; }
.tag { padding: 4rpx 16rpx; border-radius: 20rpx; font-size: 22rpx; }
.signed { background: #e6fffb; color: #00b42a; }
.pending { background: #fff7e6; color: #ff7d00; }
.info { font-size: 26rpx; color: #666; margin-bottom: 6rpx; }

/* 二维码 */
.qrcode-section {
  margin-top: 20rpx;
  text-align: center;
  background: #f9f9f9;
  border-radius: 16rpx;
  padding: 20rpx;
}
.qrcode-img {
  width: 280rpx;
  height: 280rpx;
  display: block;
  margin: 0 auto;
  border-radius: 12rpx;
}
.qrcode-hint {
  font-size: 22rpx;
  color: #999;
  margin-top: 10rpx;
  display: block;
}
.qrcode-actions {
  display: flex;
  justify-content: center;
  gap: 20rpx;
  margin-top: 15rpx;
}
.action-btn-small {
  background: #f0f0f0;
  border: none;
  border-radius: 30rpx;
  padding: 10rpx 25rpx;
  font-size: 24rpx;
  color: #333;
}
.print-btn {
  background: #007aff;
  color: #fff;
}
.empty { text-align: center; color: #999; padding: 60rpx 0; }

/* 打印提示弹窗 */
.print-tip-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}
.print-tip-card {
  background: #fff;
  border-radius: 20rpx;
  padding: 40rpx;
  width: 85%;
  max-width: 600rpx;
}
.tip-title {
  font-size: 32rpx;
  font-weight: bold;
  text-align: center;
  display: block;
  margin-bottom: 30rpx;
}
.tip-content {
  margin-bottom: 30rpx;
}
.tip-step {
  font-size: 28rpx;
  color: #666;
  display: block;
  margin-bottom: 15rpx;
  line-height: 1.6;
}
.tip-close-btn {
  background: #007aff;
  color: #fff;
  border: none;
  border-radius: 40rpx;
  padding: 15rpx;
  font-size: 28rpx;
  text-align: center;
}
</style>