# 螺蛳粉溯源系统

螺蛳粉生产流通溯源平台：产品贴码、发货签收全程记录，消费者扫码即可追溯生产批次与流通路径。Laravel 管理后台 + 微信小程序双端。

## 功能特性

- **扫码溯源** — 小程序扫描产品二维码，展示生产批次、企业信息与流通记录
- **发货管理** — 企业录入发货单，批量生成/重生成发货二维码
- **签收确认** — 收货方小程序端签收，自动写入溯源时间线
- **企业/部门/收货单位管理** — 组织架构与上下游单位维护
- **审计日志** — 发货、签收等关键操作全程留痕
- **OCR 识别** — 集成百度 OCR 提取单据信息（API Key 通过环境变量配置）

## 技术栈

| 端 | 技术 |
|----|------|
| 后台 | Laravel 5.8 + MySQL |
| API 认证 | Laravel Passport（OAuth2） |
| 小程序端 | uni-app（Vue 2 语法）+ 微信小程序 |
| 图像处理 | intervention/image + 百度 OCR |

## 快速开始

### 环境要求

- PHP ≥ 7.1.3（含 mbstring、openssl、pdo_mysql、gd 扩展）
- MySQL 5.7+
- Composer
- HBuilderX + 微信开发者工具（小程序端）

### 后台启动

```bash
cd LuoShiFencms/LuoShiFencms
composer install
cp .env.example .env          # 配置数据库、百度 OCR 凭证（BAIDU_OCR_API_KEY / SECRET）
php artisan key:generate
php artisan passport:install  # 生成 OAuth2 客户端
php artisan migrate --seed
php artisan serve
```

### 小程序端启动

1. HBuilderX 打开 `LuoShiFenApp/` 目录
2. 配置 `manifest.json` 微信小程序 appid
3. 修改 API 请求地址指向本地后台
4. 运行到微信开发者工具

## 项目结构

```
├── LuoShiFencms/            # Laravel 后台
│   ├── LuoShiFencms/        # 应用代码（模型 / 控制器 / 路由 / 迁移）
│   ├── composer.json        # 项目依赖
│   └── 数据库表结构说明文档.pdf
├── LuoShiFenApp/            # uni-app 微信小程序
│   └── pages/               # home 首页 / detail 溯源详情 / dispatch 发货
│                            # sign 签收 / login 登录 / index 索引
└── 使用说明书.docx           # 用户操作手册
```

## 测试

```bash
cd LuoShiFencms/LuoShiFencms && ./vendor/bin/phpunit
```

## 文档

- `使用说明书.docx` — 用户操作手册
- `数据库表结构说明文档.pdf` — 数据表结构说明

## License

Copyright © 2026 随风逐水。保留所有权利。
