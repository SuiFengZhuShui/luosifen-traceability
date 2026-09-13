<p align="center">
  <img src="LuoShiFenApp/static/index.png" width="120" height="120" style="border-radius: 20px;" alt="螺蛳粉溯源系统">
</p>

<h1 align="center">螺蛳粉溯源系统</h1>

<p align="center">螺蛳粉生产流通溯源平台 · 一单一码，发货签收全程留痕</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-%E2%89%A57.1.3-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP >= 7.1.3">
  <img src="https://img.shields.io/badge/Laravel-5.8-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 5.8">
  <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 5.7+">
  <img src="https://img.shields.io/badge/Token-Passport-3C4B64?style=flat-square" alt="Laravel Passport">
  <img src="https://img.shields.io/badge/MiniProgram-uni--app-07C160?style=flat-square&logo=wechat&logoColor=white" alt="uni-app 微信小程序">
  <img src="https://img.shields.io/badge/License-All%20Rights%20Reserved-lightgrey?style=flat-square" alt="License">
</p>

## 项目简介

面向螺蛳粉生产企业的流通溯源系统。每张发货单生成一个专属二维码，码的内容就是该单的签收链接；收货方扫码即可登记实收数量并手写电子签名，系统自动把签收节点追加进溯源时间线。消费者和管理者通过微信小程序可查看批次号、生产日期、产品规格与上下游信息。

后台按角色分为四套入口：超级管理员管企业，企业管理员管员工 / 部门 / 收货单位与发货记录，发货员用移动端页面录单，收货方扫码即可签收（无需登录）。

## 功能简介

- **一单一码** — 每张发货单生成一个二维码，内容为该单的签收链接（`/sign/{发货单ID}`）。二维码图片存于 `storage/app/public/qrcodes/`，可批量重生成。
- **扫码签收（免登录）** — 收货方用手机扫二维码，浏览器直接打开签收表单，填写**实收数量**并**手写电子签名**（签名图片存档）。已签收的码再次打开会显示签收记录，避免重复操作。
- **溯源查询** — 小程序请求同一链接返回 JSON，展示销售单号、产品名称、规格、数量、**批次号**、收货单位等流通信息。
- **发货登记 + OCR** — 发货员在移动端录入发货单（含销售单号、产品、规格、数量、批次号、生产日期、收货单位），支持拍照存档；集成**百度 OCR** 识别单据照片自动填表，减少手工录入。
- **二维码重生成** — 命令行 `php artisan qrcode:regenerate` 批量重发所有发货单二维码（域名变更或码损坏时使用）。
- **组织架构管理** — 超级管理员维护企业并为企业创建管理员账号、启停企业；企业管理员维护员工、部门、收货单位（支持重置收货单位密码）。
- **数据导出** — 发货记录一键导出，便于对账存档。
- **审计日志** — 发货、签收等关键操作写入 `audit_logs`，可回溯操作人与时间点。
- **四角色权限** — `super_admin`（超级管理员）/ `company_admin`（企业管理员）/ `dispatcher`（发货员）/ `receiver_admin`（收货单位管理员），由 `CheckRole` 中间件按路由隔离。
- **双通道接口** — Web 后台用 Blade 会话认证；小程序走 `routes/api.php`，Laravel Passport 个人访问令牌（Personal Access Token）认证。

## 技术栈

| 端 | 技术 |
|----|------|
| 后台 | Laravel 5.8 + MySQL 5.7+ |
| 后台界面 | Blade 模板（含独立移动端页面 `views/mobile`） |
| API 认证 | Laravel Passport（Personal Access Token，`auth:api` guard） |
| 小程序端 | uni-app（Vue 2 语法）+ 微信小程序 |
| 二维码 | simplesoftwareio/simple-qrcode |
| 图像处理 | intervention/image |
| 文字识别 | 百度 OCR（通用文字识别，凭证走环境变量） |

## 快速体验

### 环境要求

- PHP ≥ 7.1.3，需开启 `mbstring`、`openssl`、`pdo_mysql`、`gd` 扩展
- MySQL 5.7+
- Composer
- HBuilderX + 微信开发者工具（运行小程序端）

### 后台启动

```bash
cd LuoShiFencms/LuoShiFencms
composer install
cp .env.example .env          # 配置数据库；SEED_PASSWORD 与百度 OCR 见下
php artisan key:generate
php artisan passport:install  # 生成 Passport 密钥与令牌客户端，必执行
php artisan storage:link      # 二维码与发货照片位于 storage/app/public，必执行
php artisan migrate --seed    # 建表 + 写入演示数据
php artisan qrcode:regenerate # 按 APP_URL 批量生成发货单二维码图片
php artisan serve
```

### 演示账号

`migrate --seed` 会写入一套完整演示数据：4 家企业、5 个部门、6 个收货单位、8 个账号、9 张发货单（6 待签 + 3 已签）、3 条签收记录与若干审计日志，登录后页面即有内容可看。

账号统一密码取自 `.env` 的 **`SEED_PASSWORD`**（`.env.example` 默认 `CHANGE_ME`）。

| 角色 | 账号 | 登录后入口 |
|------|------|-----------|
| 超级管理员 | `admin` | `/super-admin/dashboard` |
| 企业管理员 | `zhongliuadmin` / `huguiadmin` / `leitingadmin` | `/company-admin/dashboard` |
| 发货员 | `zhangsan` / `lisi` / `wangwu` / `zhaoliu` | `/mobile/home` |

> **生产环境务必改掉 `SEED_PASSWORD` 并重建账号。**
> 收货单位不通过后台登录 —— 它们的签收入口是公开的 `/sign/{发货单ID}` 页面，扫码即达，无需账号。

### 百度 OCR 配置（可选）

`.env.example` 已留好三个空占位键，填上即可；缺任意一个，OCR 接口会返回「百度 OCR 未配置」：

```dotenv
BAIDU_OCR_APP_ID=你的应用ID
BAIDU_OCR_API_KEY=你的API Key
BAIDU_OCR_SECRET_KEY=你的Secret Key
```

三个值都在百度智能云控制台的「文字识别」应用里取。不配也能跑，只是发货单 OCR 自动识别不可用，需手工录入。

### 四个入口

| 入口 | 路径 | 角色 |
|------|------|------|
| 超级管理员 | `/super-admin/dashboard` | `super_admin` |
| 企业管理员 | `/company-admin/dashboard` | `company_admin` |
| 发货员移动端 | `/mobile/home` | `dispatcher` |
| 收货方签收页 | `/sign/{发货单ID}` | 公开，无需登录 |

### 小程序端启动

1. HBuilderX 打开 `LuoShiFenApp/` 目录
2. 配置 `manifest.json` 微信小程序 appid
3. 修改 API 请求地址指向本地后台
4. 运行到微信开发者工具

## 安装教程

部署要点：**Web 根目录指向 `LuoShiFencms/LuoShiFencms/public`**。这是双层同名目录，配置时容易指错一层，指到外层会把 `.env` 和 `vendor/` 暴露出去。

### 伪静态配置（Nginx）

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/LuoShiFencms/LuoShiFencms/public;   # 注意是双层目录的里层 public
    index index.php;

    # 拒绝隐藏文件（保留 ACME 验证目录）
    location ~ /\.(?!well-known).* { deny all; }

    # 拒绝敏感后缀
    location ~* \.(env|log|sql|sqlite|db|bak|old|save|swp|tmp|ini|lock)$ { deny all; }
    location ~* (composer\.(json|lock)|package(-lock)?\.json)$ { deny all; }
    location ~ ^/(vendor|node_modules)/ { deny all; }

    # 二维码与发货照片（执行 storage:link 后对外可见）
    location /storage/ {
        alias /var/www/LuoShiFencms/LuoShiFencms/storage/app/public/;
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        # 按实际 PHP 版本调整 socket 路径
        fastcgi_pass unix:/var/run/php/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 伪静态配置（IIS）

在 `public/` 目录放 `web.config`：

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
  <system.webServer>
    <rewrite>
      <rules>
        <rule name="Laravel" stopProcessing="true">
          <match url="^(.*)$" />
          <conditions logicalGrouping="MatchAll">
            <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
          </conditions>
          <action type="Rewrite" url="index.php/{R:1}" />
        </rule>
      </rules>
    </rewrite>
  </system.webServer>
</configuration>
```

### Apache

`public/.htaccess` 已内置，确认 `mod_rewrite` 已开启即可。

### 部署后必做

```bash
php artisan passport:install   # 生产环境同样需要，生成令牌密钥与客户端
php artisan storage:link       # 否则二维码图片与发货照片全部 404
php artisan config:cache       # 改 .env 后需 config:clear 再重新 cache
php artisan migrate --force    # 生产环境迁移需加 --force
```

生产库**不要**带 `--seed` —— 演示数据只用于本地和验收环境，且请先改掉 `SEED_PASSWORD`。若已误写入演示数据，删库重建即可。

二维码内容用的是 `url('/sign/' . $id)`，**依赖 `.env` 里的 `APP_URL`** —— 上线后务必把 `APP_URL` 改成正式域名，否则已生成二维码指向的还是旧地址。改完域名用 `php artisan qrcode:regenerate` 批量重发。

## 项目结构

```
├── LuoShiFencms/                    # Laravel 后台
│   ├── LuoShiFencms/                # 应用代码
│   │   ├── app/
│   │   │   ├── Http/Controllers/
│   │   │   │   ├── SuperAdmin/      # 超级管理员（企业管理）
│   │   │   │   ├── CompanyAdmin/    # 企业管理员（员工/部门/收货单位/发货记录/导出）
│   │   │   │   ├── Api/             # 小程序接口（Auth/Dispatch/Sign）
│   │   │   │   └── Auth/            # 登录注册
│   │   │   ├── Console/Commands/    # qrcode:regenerate 二维码重生成
│   │   │   └── (Enterprise/Department/ReceivingUnit/DispatchRecord/SignRecord/AuditLog)
│   │   ├── database/
│   │   │   ├── migrations/          # users + 6 张业务表（enterprises/departments/
│   │   │   │                        # receiving_units/dispatch_records/sign_records/audit_logs）
│   │   │   └── seeds/               # LuoshifenSeeder 演示数据
│   │   ├── resources/views/
│   │   │   ├── super/               # 超管后台视图
│   │   │   ├── company/             # 企业后台视图
│   │   │   ├── mobile/              # 发货员移动端页面
│   │   │   └── sign/                # 扫码签收页（form / already_signed）
│   │   └── routes/                  # web.php + api.php
│   ├── composer.json
│   └── 数据库表结构说明文档.pdf
├── LuoShiFenApp/                    # uni-app 微信小程序
│   ├── pages/                       # home 首页 / detail 溯源详情 / dispatch 发货
│   │                                # sign 签收 / login 登录 / index 索引
│   └── static/                      # 图标与静态资源
└── 使用说明书.docx                   # 用户操作手册
```

## 测试

```bash
cd LuoShiFencms/LuoShiFencms && ./vendor/bin/phpunit
```

## 文档

- `使用说明书.docx` — 用户操作手册
- `数据库表结构说明文档.pdf` — 数据表结构说明

## 声明

> 本项目为个人学习与实践作品，著作权归作者所有。
>
> 源码仅供学习、研究与技术交流使用。未经授权，不得用于任何商业用途，包括但不限于搭建线上平台对外经营、提供付费服务、二次销售或整体/部分纳入商业产品。
>
> 使用者应自行遵守所在国家或地区的法律法规。因使用本项目产生的一切后果，由使用者自行承担。
>
> 使用本项目即表示您已充分理解并同意本声明的全部内容。

---

Copyright © 2026 随风逐水。保留所有权利。
