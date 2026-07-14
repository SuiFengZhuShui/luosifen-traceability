const BASE_URL = 'http://127.0.0.1/api';

// 统一提示函数：2秒后自动消失
function showToast(title, icon = 'none') {
    uni.showToast({ title, icon, duration: 2000 });
}

const request = (options) => {
    return new Promise((resolve, reject) => {
        const token = uni.getStorageSync('token');
        uni.request({
            url: BASE_URL + options.url,
            method: options.method || 'GET',
            data: options.data || {},
            header: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': token ? 'Bearer ' + token : ''
            },
            success: (res) => {
                if (res.statusCode === 200) {
                    resolve(res.data);
                } else if (res.statusCode === 401) {
                    uni.removeStorageSync('token');
                    uni.removeStorageSync('userName');
                    uni.reLaunch({ url: '/pages/login/login' });
                } else {
                    showToast(res.data.message || '请求失败');
                    reject(res);
                }
            },
            fail: (err) => {
                showToast('网络异常，请检查后端是否启动');
                reject(err);
            }
        });
    });
};

export default request;