// api/index.js
import request from '../utils/request.js';

export const login = (data) => request({ url: '/login', method: 'POST', data });
export const getDispatchRecords = () => request({ url: '/dispatch/records' });
export const getReceivingUnits = () => request({ url: '/receiving-units' });
export const createDispatch = (data) => request({ url: '/dispatch', method: 'POST', data });
export const ocrRecognize = (data) => request({ url: '/dispatch/ocr', method: 'POST', data });

// 签收（免登录，直接请求公开路由）
export const submitSign = (id, data) => {
  return new Promise((resolve, reject) => {
    uni.request({
      url: `http://127.0.0.1/sign/${id}`,   
      method: 'POST',
      data: data,
      header: { 'Content-Type': 'application/json' },
      success: (res) => resolve(res.data),
      fail: (err) => reject(err)
    });
  });
};
