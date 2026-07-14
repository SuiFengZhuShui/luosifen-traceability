<template>
  <view class="page">
    <view class="card">
      <view class="title">新建发货</view>

      <!-- 拍照识别区域 -->
      <view class="ocr-section">
        <view class="photo-area" @click="takePhoto">
          <image v-if="photoPath" :src="photoPath" mode="aspectFill" class="photo" />
          <view v-else class="placeholder">
            <text class="camera-icon">📷</text>
            <text>点击拍摄销售单</text>
          </view>
        </view>
        <button v-if="photoPath" class="ocr-btn" @click="doOCR" :disabled="ocrLoading">
          {{ ocrLoading ? '识别中...' : '识别销售单信息' }}
        </button>
      </view>

      <!-- 手动补充信息 -->
      <view class="form-card">
        <input class="input" v-model="form.sales_order_no" placeholder="销售单号" />
        <input class="input" v-model="form.buyer_name" placeholder="购买方名称" />
        <input class="input" v-model="form.product_name" placeholder="产品名称" />
        <input class="input" v-model="form.spec" placeholder="规格" />
        <input class="input" v-model="form.quantity" type="number" placeholder="数量" />
        <input class="input" v-model="form.batch_no" placeholder="产品批次号" />
        <input class="input" v-model="form.production_date" type="date" placeholder="生产日期" />

        <!-- 选择收货单位 -->
        <picker :range="unitNames" @change="onUnitChange">
          <view class="picker">{{ selectedUnit || '请选择收货单位' }}</view>
        </picker>
      </view>

      <button class="btn" @click="submit">提交发货</button>
    </view>
  </view>
</template>

<script>
import { getReceivingUnits, ocrRecognize, createDispatch } from '@/api/index.js';

export default {
  data() {
    return {
      photoPath: '',
      ocrLoading: false,
      units: [],
      unitNames: [],
      form: {
        sales_order_no: '',
        buyer_name: '',
        product_name: '',
        spec: '',
        quantity: '',
        batch_no: '',
        production_date: '',
        receiving_unit_id: null
      }
    };
  },
  computed: {
    selectedUnit() {
      const unit = this.units.find(u => u.id === this.form.receiving_unit_id);
      return unit ? unit.name : '';
    }
  },
  async onShow() {
    if (!uni.getStorageSync('token')) {
      uni.reLaunch({ url: '/pages/login/login' });
      return;
    }
    try {
      const res = await getReceivingUnits();
      this.units = Array.isArray(res) ? res : [];
      this.unitNames = this.units.map(u => u.name);
    } catch (e) {
      console.error('获取收货单位失败', e);
    }
  },
  methods: {
    // 拍照
    takePhoto() {
      uni.chooseImage({
        count: 1,
        sizeType: ['compressed'],
        sourceType: ['camera'],
        success: (res) => {
          this.photoPath = res.tempFilePaths[0];
        }
      });
    },

	async doOCR() {
		this.ocrLoading = true;
		try {
			const fs = uni.getFileSystemManager();
			const base64 = fs.readFileSync(this.photoPath, 'base64');

			const res = await ocrRecognize({ image: base64 });
			
			this.form.sales_order_no = res.sales_order_no || '';
			this.form.buyer_name = res.buyer_name || '';
			this.form.product_name = res.product_name || '';
			this.form.spec = res.spec || '';
			this.form.quantity = res.quantity || 0;
			
			uni.showToast({ title: '识别成功', icon: 'success' });
		} catch (e) {
			uni.showToast({ title: '识别失败，请手动填写', icon: 'none' });
		} finally {
			this.ocrLoading = false;
		}
	},

    // 选择收货单位
    onUnitChange(e) {
      const idx = e.detail.value;
      if (this.units[idx]) {
        this.form.receiving_unit_id = this.units[idx].id;
      }
    },

    // 提交发货
    async submit() {
      if (!this.form.batch_no || !this.form.receiving_unit_id) {
        uni.showToast({ title: '请填写批次号和选择收货单位', icon: 'none' });
        return;
      }
      if (!this.form.quantity || this.form.quantity <= 0) {
        uni.showToast({ title: '请输入有效数量', icon: 'none' });
        return;
      }
      try {
        const res = await createDispatch(this.form);
        if (res.success) {
          uni.showToast({ title: '发货成功', icon: 'success' });
          uni.navigateTo({
            url: `/pages/dispatch/detail?id=${res.dispatch_id}&qrcode_url=${encodeURIComponent(res.qrcode_url || '')}`
          });
        } else {
          uni.showToast({ title: '提交失败', icon: 'none' });
        }
      } catch (e) {
        uni.showToast({ title: '网络错误', icon: 'none' });
      }
    }
  }
};
</script>

<style scoped>
.page {
  background: #f0f4ff;
  min-height: 100vh;
  padding: 30rpx;
}

.card {
  background: #fff;
  border-radius: 30rpx;
  padding: 40rpx;
}

.title {
  font-size: 36rpx;
  font-weight: bold;
  text-align: center;
  margin-bottom: 40rpx;
}

/* 拍照区域 */
.ocr-section {
  margin-bottom: 30rpx;
}

.photo-area {
  height: 400rpx;
  border: 2rpx dashed #ccc;
  border-radius: 20rpx;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20rpx;
  overflow: hidden;
}

.photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.placeholder {
  text-align: center;
  color: #aaa;
}

.camera-icon {
  font-size: 60rpx;
  display: block;
  margin-bottom: 10rpx;
}

.ocr-btn {
  background: #007aff;
  color: #fff;
  border: none;
  border-radius: 50rpx;
  height: 80rpx;
  line-height: 80rpx;
  font-size: 28rpx;
}

/* 表单 */
.form-card {
  margin-bottom: 20rpx;
}

.input {
  border-bottom: 1px solid #eee;
  padding: 20rpx 0;
  margin-bottom: 30rpx;
  font-size: 30rpx;
}

.picker {
  border-bottom: 1px solid #eee;
  padding: 20rpx 0;
  margin-bottom: 30rpx;
  color: #888;
  font-size: 30rpx;
}

.btn {
  background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
  color: #fff;
  border: none;
  border-radius: 50rpx;
  height: 90rpx;
  line-height: 90rpx;
  font-size: 32rpx;
  margin-top: 20rpx;
}

uni-picker-view-column {
  font-size: 50rpx;
}
</style>