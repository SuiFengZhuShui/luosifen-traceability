<template>
  <view class="page">
    <view class="card">
      <!-- 已签收提示 -->
      <view v-if="alreadySigned" class="signed-notice">
        <text class="notice-icon">✅</text>
        <text class="notice-title">该记录已完成签收</text>
        <text class="notice-info" v-if="signedInfo.signed_at">签收时间：{{ signedInfo.signed_at }}</text>
      </view>

      <!-- 签收表单 -->
      <view v-else>
        <text class="title">收货签收</text>

        <!-- 发货信息 -->
        <view class="info-section">
          <view class="info-row">
            <text class="info-label">销售单号</text>
            <text class="info-value">{{ dispatch.sales_order_no || '-' }}</text>
          </view>
          <view class="info-row">
            <text class="info-label">产品名称</text>
            <text class="info-value">{{ dispatch.product_name || '-' }}</text>
          </view>
          <view class="info-row">
            <text class="info-label">发货数量</text>
            <text class="info-value">{{ dispatch.quantity || 0 }}</text>
          </view>
          <view class="info-row">
            <text class="info-label">收货单位</text>
            <text class="info-value">{{ dispatch.receiving_unit_name || '-' }}</text>
          </view>
        </view>

        <!-- 签收表单：仅保留实收数量和签名 -->
        <view class="form-section">
          <view class="form-item">
            <text class="form-label">实收数量 *</text>
            <input class="form-input" v-model="form.actual_quantity" type="number" placeholder="请输入实收数量" />
          </view>

          <!-- 手写签名 -->
          <view class="form-item">
            <text class="form-label">手写签名 *</text>
            <view class="signature-area">
              <canvas canvas-id="signCanvas" class="sign-canvas"
                      @touchstart="startSign" @touchmove="signing" @touchend="endSign"></canvas>
            </view>
            <view class="signature-actions">
              <text class="action-btn" @click="clearCanvas">清除重签</text>
            </view>
          </view>
        </view>

        <!-- 提交按钮 -->
        <button class="submit-btn" @click="submitSign" :disabled="submitting">
          {{ submitting ? '提交中...' : '确认签收' }}
        </button>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      dispatchId: '',
      dispatch: {
        sales_order_no: '',
        product_name: '',
        quantity: 0,
        receiving_unit_name: ''
      },
      form: {
        actual_quantity: 0,
        signature: ''
      },
      alreadySigned: false,
      signedInfo: { signed_at: '' },
      submitting: false,
      ctx: null,
      drawing: false
    };
  },
  
  async onLoad(options) {
    const id = options.id || '';
    if (!id) {
      uni.showToast({ title: '参数错误', icon: 'none' });
      return;
    }
    this.dispatchId = id;
    await this.loadSignInfo(id);
  },
  
  onReady() {
    setTimeout(() => {
      if (!this.alreadySigned) {
        this.initCanvas();
      }
    }, 500);
  },
  
  methods: {
    async loadSignInfo(id) {
      uni.showLoading({ title: '加载中...' });
      try {
        const res = await uni.request({
          url: `http://127.0.0.1/sign/${id}`,
          method: 'GET',
          header: { 'Accept': 'application/json' }
        });
        uni.hideLoading();
        if (res.statusCode === 200 && res.data && res.data.data) {
          const data = res.data.data;
          if (data.already_signed) {
            this.alreadySigned = true;
            this.signedInfo = { signed_at: data.sign_info?.signed_at || '' };
          } else {
            this.dispatch = data.dispatch;
            this.form.actual_quantity = data.dispatch.quantity || 0;
          }
        }
      } catch (e) {
        uni.hideLoading();
        uni.showToast({ title: '加载失败', icon: 'none' });
      }
    },

    initCanvas() {
      this.ctx = uni.createCanvasContext('signCanvas');
      this.ctx.setStrokeStyle('#333');
      this.ctx.setLineWidth(4);
      this.ctx.setLineCap('round');
    },

    startSign(e) {
      this.drawing = true;
      const point = e.touches[0];
      this.ctx.moveTo(point.x, point.y);
    },

    signing(e) {
      if (!this.drawing) return;
      const point = e.touches[0];
      this.ctx.lineTo(point.x, point.y);
      this.ctx.stroke();
      this.ctx.draw(true);
    },

    endSign() { this.drawing = false; },

    clearCanvas() {
      this.ctx.clearRect(0, 0, 350, 200);
      this.ctx.draw();
    },

    getSignature() {
      return new Promise((resolve, reject) => {
        uni.canvasToTempFilePath({
          canvasId: 'signCanvas',
          success: (res) => {
            const fs = uni.getFileSystemManager();
            const base64 = fs.readFileSync(res.tempFilePath, 'base64');
            resolve('data:image/png;base64,' + base64);
          },
          fail: reject
        });
      });
    },

    async submitSign() {
      if (!this.form.actual_quantity || this.form.actual_quantity <= 0) {
        uni.showToast({ title: '请输入实收数量', icon: 'none' });
        return;
      }

      try {
        this.submitting = true;
        const signature = await this.getSignature();
        this.form.signature = signature;

        const res = await uni.request({
          url: `http://127.0.0.1/sign/${this.dispatchId}`,
          method: 'POST',
          header: { 'Content-Type': 'application/json' },
          data: {
            actual_quantity: this.form.actual_quantity,
            receiver_name: '',      // 不收集，传空
            receiver_phone: '',     // 不收集，传空
            signature: this.form.signature
          }
        });

        if (res.data.code === 200) {
          uni.showToast({ title: '签收成功', icon: 'success' });
          this.alreadySigned = true;
          this.signedInfo = { signed_at: new Date().toLocaleString() };
        } else {
          uni.showToast({ title: res.data.message || '签收失败', icon: 'none' });
        }
      } catch (e) {
        uni.showToast({ title: '网络错误', icon: 'none' });
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>
.page { min-height: 100vh; background: #f0f4ff; padding: 40rpx; }
.card { background: #fff; border-radius: 30rpx; padding: 40rpx; }
.signed-notice { text-align: center; padding: 40rpx 0; }
.notice-icon { font-size: 80rpx; display: block; margin-bottom: 20rpx; }
.notice-title { font-size: 36rpx; font-weight: bold; color: #00b42a; display: block; margin-bottom: 20rpx; }
.notice-info { font-size: 26rpx; color: #666; margin-bottom: 8rpx; display: block; }
.title { font-size: 36rpx; font-weight: bold; text-align: center; margin-bottom: 30rpx; color: #333; }
.info-section { background: #f9f9f9; border-radius: 16rpx; padding: 20rpx; margin-bottom: 30rpx; }
.info-row { display: flex; justify-content: space-between; padding: 12rpx 0; border-bottom: 1rpx solid #eee; }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 26rpx; color: #999; }
.info-value { font-size: 26rpx; color: #333; font-weight: 500; }
.form-section { margin-bottom: 20rpx; }
.form-item { margin-bottom: 30rpx; }
.form-label { font-size: 28rpx; color: #333; margin-bottom: 12rpx; display: block; font-weight: 500; }
.form-input { border: 1px solid #ddd; border-radius: 12rpx; padding: 20rpx; font-size: 30rpx; background: #fafafa; }
.signature-area { border: 1px solid #ddd; border-radius: 12rpx; overflow: hidden; background: #fff; }
.sign-canvas { width: 100%; height: 200px; }
.signature-actions { text-align: right; margin-top: 12rpx; }
.action-btn { font-size: 26rpx; color: #007aff; padding: 8rpx 20rpx; }
.submit-btn { background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%); color: #fff; border: none; border-radius: 50rpx; height: 90rpx; line-height: 90rpx; font-size: 32rpx; font-weight: bold; margin-top: 10rpx; }
.submit-btn[disabled] { opacity: 0.5; }
</style>