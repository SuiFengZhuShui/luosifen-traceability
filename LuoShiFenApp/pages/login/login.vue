<template>
  <view class="page">
    <view class="logo">
      <image src="/static/index.png" mode="aspectFit" class="logo-img" />
      <text class="title">螺蛳粉溯源</text>
    </view>

    <view class="form">
      <uni-easyinput
        v-model="account"
        placeholder="请输入账号"
        prefixIcon="person"
        :inputBorder="false"
        class="input-item"
      />
      <uni-easyinput
        v-model="password"
        type="password"
        placeholder="请输入密码"
        prefixIcon="locked"
        :inputBorder="false"
        class="input-item"
      />

      <!-- 隐私协议勾选框（优化间距） -->
      <view class="agreement">
        <checkbox-group @change="checkboxChange">
          <label class="agreement-label">
            <checkbox value="agree" :checked="isAgree" color="#007aff" />
            <text class="agreement-text">我已阅读并同意</text>
            <text class="link" @click.stop="showProtocol('service')">《用户服务协议》</text>
            <text class="agreement-text">和</text>
            <text class="link" @click.stop="showProtocol('privacy')">《隐私政策》</text>
          </label>
        </checkbox-group>
      </view>

      <button class="btn" @click="handleLogin" :disabled="!isAgree">登 录</button>
    </view>
  </view>
</template>

<script>
export default {
  data() {
    return {
      account: '',
      password: '',
      isAgree: false
    };
  },
  methods: {
    checkboxChange(e) {
      this.isAgree = e.detail.value.includes('agree');
    },
    showProtocol(type) {
      const title = type === 'service' ? '用户服务协议' : '隐私政策';
      const content = type === 'service' 
        ? '本协议是您与螺蛳粉溯源系统之间的协议。\n1. 服务内容：提供物流签收管理。\n2. 用户账号：管理员分配的账号仅限本人使用。\n3. 数据安全：所有数据加密存储，不会向第三方泄露。'
        : '我们收集的信息仅用于：\n1. 用户登录验证。\n2. 发货记录与签收凭证保存。\n3. 不会收集位置、通讯录等无关信息。\n4. 数据仅保存在本企业服务器，不对外共享。';
      uni.showModal({
        title: title,
        content: content,
        showCancel: false,
        confirmText: '我知道了'
      });
    },
    handleLogin() {
      if (!this.account || !this.password) {
        uni.showToast({ title: '请输入账号和密码', icon: 'none', duration: 2000 });
        return;
      }
      if (!this.isAgree) {
        uni.showToast({ title: '请阅读并同意协议', icon: 'none', duration: 2000 });
        return;
      }

      uni.showLoading({ title: '登录中...' });
      uni.request({
        url: 'http://127.0.0.1/api/login',
        method: 'POST',
        header: { 'Content-Type': 'application/json' },
        data: { account: this.account, password: this.password },
        success: (res) => {
          uni.hideLoading();
          if (res.statusCode === 200 && res.data.token) {
            uni.setStorageSync('token', res.data.token);
            uni.setStorageSync('userName', res.data.user?.name || '');
            uni.switchTab({ url: '/pages/home/home' });
          } else {
            uni.showToast({ title: res.data.error || '账号或密码错误', icon: 'none' });
          }
        },
        fail: () => {
          uni.hideLoading();
          uni.showToast({ title: '网络异常', icon: 'none' });
        }
      });
    }
  }
};
</script>

<style scoped>
.page {
  min-height: 100vh;
  height: 100vh;
  overflow: hidden;
  background: linear-gradient(135deg, #007aff, #5673ff);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 12vh 0 0;          
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
}

.logo {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 40rpx;       
}

.logo-img {
  width: 140rpx;
  height: 140rpx;
  border-radius: 50%;
  background: #fff;
  padding: 8rpx;
}

.title {
  color: #fff;
  font-size: 38rpx;
  font-weight: bold;
  margin-top: 16rpx;
}

.form {
  width: 90%;
  max-width: 600rpx;
  background: #fff;
  border-radius: 30rpx;
  padding: 50rpx 40rpx;       
  box-shadow: 0 10rpx 30rpx rgba(0, 0, 0, 0.1);
}

.input-item {
  margin-bottom: 36rpx;        
}

.agreement {
  margin: 0 0 30rpx 0;
  line-height: 1.6;
}

.agreement-label {
  font-size: 24rpx;
  color: #666;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
}

.agreement-text {
  font-size: 24rpx;
}

.link {
  color: #007aff;
  font-size: 24rpx;
}

.btn {
  background: linear-gradient(135deg, #007aff, #5673ff);
  color: #fff;
  border-radius: 50rpx;
  height: 100rpx;
  line-height: 100rpx;
  margin-top: 20rpx;
  font-size: 36rpx;
  font-weight: bold;
  border: none;
}

.btn[disabled] {
  opacity: 0.4;
  background: #ccc;
}
</style>