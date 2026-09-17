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

        <!-- 签收表单 -->
        <view class="form-section">
          <view class="form-item">
            <text class="form-label">实收数量 *</text>
            <input class="form-input" v-model="form.actual_quantity" type="number" placeholder="请输入实收数量" />
          </view>

          <!-- 手写签名：点击放大 -->
          <view class="form-item">
            <text class="form-label">手写签名 *</text>
            <view class="signature-preview" @click="showSignModal = true">
              <image v-if="form.signature" :src="form.signature" mode="widthFix" class="sign-preview-img" />
              <view v-else class="sign-placeholder">
                <text class="sign-placeholder-icon">✍️</text>
                <text class="sign-placeholder-text">点击此处手写签名</text>
              </view>
            </view>
            <view v-if="form.signature" class="signature-actions">
              <text class="action-btn" @click="showSignModal = true">重新签名</text>
            </view>
          </view>
        </view>

        <!-- 提交按钮 -->
        <button class="submit-btn" @click="submitSign" :disabled="submitting">
          {{ submitting ? '提交中...' : '确认签收' }}
        </button>
      </view>
    </view>

    <!-- 全屏签名弹窗 -->
    <view v-if="showSignModal" class="sign-modal" @touchmove.stop.prevent>
      <view class="sign-modal-header">
        <text class="modal-title">手写签名</text>
        <text class="modal-close" @click="showSignModal = false">取消</text>
      </view>
      <view class="sign-modal-body">
        <canvas canvas-id="signCanvas" class="sign-canvas"
                @touchstart="startSign" @touchmove="signing" @touchend="endSign"></canvas>
      </view>
      <view class="sign-modal-footer">
        <button class="modal-btn btn-clear" @click="clearCanvas">清除</button>
        <button class="modal-btn btn-confirm" @click="confirmSign">确认</button>
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
      showSignModal: false,
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

    confirmSign() {
      uni.canvasToTempFilePath({
        canvasId: 'signCanvas',
        success: (res) => {
          const fs = uni.getFileSystemManager();
          const base64 = fs.readFileSync(res.tempFilePath, 'base64');
          this.form.signature = 'data:image/png;base64,' + base64;
          this.showSignModal = false;
        },
        fail: () => {
          uni.showToast({ title: '获取签名失败', icon: 'none' });
        }
      });
    },

    async submitSign() {
      if (!this.form.actual_quantity || this.form.actual_quantity <= 0) {
        uni.showToast({ title: '请输入实收数量', icon: 'none' });
        return;
      }
      if (!this.form.signature) {
        uni.showToast({ title: '请手写签名', icon: 'none' });
        return;
      }

      try {
        this.submitting = true;

        const res = await uni.request({
          url: `http://127.0.0.1/sign/${this.dispatchId}`,
          method: 'POST',
          header: { 'Content-Type': 'application/json' },
          data: {
            actual_quantity: this.form.actual_quantity,
            receiver_name: '',
            receiver_phone: '',
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
  },

  watch: {
    showSignModal(val) {
      if (val) {
        this.$nextTick(() => {
          setTimeout(() => {
            this.initCanvas();
          }, 200);
        });
      }
    }
  }
};
</script>

<style scoped>
.page { background: #f0f4ff; padding: 40rpx; min-height: 100vh; }
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

/* 签名预览区域 */
.signature-preview {
  border: 2rpx dashed #ccc;
  border-radius: 12rpx;
  background: #fafafa;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200rpx;
  overflow: hidden;
}
.sign-preview-img { width: 100%; }
.sign-placeholder { text-align: center; padding: 40rpx 0; }
.sign-placeholder-icon { font-size: 60rpx; display: block; margin-bottom: 12rpx; }
.sign-placeholder-text { font-size: 28rpx; color: #999; }
.signature-actions { text-align: right; margin-top: 12rpx; }
.action-btn { font-size: 26rpx; color: #007aff; padding: 8rpx 20rpx; }

/* 全屏签名弹窗 */
.sign-modal {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: #fff; z-index: 999;
  display: flex; flex-direction: column;
}
.sign-modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20rpx 40rpx; border-bottom: 1rpx solid #eee;
}
.modal-title { font-size: 34rpx; font-weight: bold; }
.modal-close { font-size: 30rpx; color: #999; }
.sign-modal-body {
  flex: 1; padding: 40rpx;
  display: flex; justify-content: center; align-items: center;
}
.sign-canvas { width: 100%; height: 500rpx; border: 1rpx solid #ddd; border-radius: 12rpx; background: #fff; }
.sign-modal-footer {
  display: flex; gap: 20rpx; padding: 20rpx 40rpx 60rpx;
}
.modal-btn { flex: 1; height: 90rpx; line-height: 90rpx; text-align: center; border-radius: 50rpx; font-size: 32rpx; border: none; }
.btn-clear { background: #f0f0f0; color: #666; }
.btn-confirm { background: linear-gradient(135deg, #3a7bd5, #00d2ff); color: #fff; }

.submit-btn { background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%); color: #fff; border: none; border-radius: 50rpx; height: 90rpx; line-height: 90rpx; font-size: 32rpx; font-weight: bold; margin-top: 10rpx; }
.submit-btn[disabled] { opacity: 0.5; }
</style>
