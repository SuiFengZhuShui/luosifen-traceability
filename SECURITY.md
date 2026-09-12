# 安全说明

本仓库启用了 GitHub Dependabot 依赖扫描。当前 **92 条告警已全部标记为 ignored**，
原因记录如下。

## 告警清单（全部为 composer，后台 `LuoShiFencms/`）

| 包 | 条数 | 当前版本 | 修复所需版本 |
|----|-----|---------|-------------|
| `phpoffice/phpspreadsheet` | 28 | 1.25.2 | 1.2x 后续版本 |
| `guzzlehttp/guzzle` | 18 | 6.5.8 | **7.x** |
| `laravel/framework` | 10 | 5.8.38 | 最高需 **12.61.1** |
| `guzzlehttp/psr7` | 8 | 1.9.1 | 2.x |
| `symfony/http-foundation` | 4 | 4.4.49 | 5.4.50 |
| `symfony/process` | 4 | 4.4.44 | 5.4.51 |
| `symfony/mime` | 4 | 4.4.x | 5.4.x |
| `firebase/php-jwt` | 4 | 5.5.1 | 6.x |
| `phpseclib/phpseclib` | 3 | 2.0.53 | 3.x |
| `symfony/routing` | 2 | 4.4.44 | 5.4.52 |
| `symfony/polyfill-intl-idn` | 2 | 1.2x | 1.30.x |
| `illuminate/database` | 2 | 5.8.x | （随 Laravel 升级） |
| `psy/psysh` | 1 | 0.9.12 | 0.11.23 |
| `phpunit/phpunit` | 1 | 8.5.x | 9.x |
| `maatwebsite/excel` | 1 | 3.1.56 | 3.1.x 后续版本 |

其中 `phpspreadsheet` 与 `maatwebsite/excel` 由 Excel 导出功能引入，
`guzzle` / `guzzlehttp/psr7` 由 HTTP 客户端与 Passport 间接引入。

## 为什么没有修复

本项目刻意固定在 **Laravel 5.8 + PHP 7.1.3** 这套技术栈上（课程 / 学习阶段的技术选型）。
这些告警**无法通过升级依赖版本来消除**：

- Laravel 自身的告警最高要求 **12.61.1** —— 意味着要跨 5 → 6 → … → 12 共 7 个大版本，
  属重写级别改造；
- Symfony 组件的修复版本是 **5.4.x**，而 Laravel 5.8 把 `symfony/*` 锁在 `^4.3`，装不上 5.4；
  且 Symfony 4.4 已结束维护，官方不再回移补丁；
- `guzzle` 的修复版本在 **7.x**，而 Laravel 5.8 依赖链锁在 `^6.3`，同为死路；
- `guzzlehttp/psr7` 2.x、`firebase/php-jwt` 6.x、`phpseclib` 3.x 都会牵动 Laravel 的依赖约束；
- `psy/psysh` 的修复版本要求 **PHP ≥ 8.0**，同时 `laravel/tinker ^1.0` 把 psysh 锁在 `^0.9`。

**结论：只有把整套栈升级到 Laravel 12 + PHP 8.2 才能清零这些告警。**

## 已经实际清掉的部分

排查中发现 `LuoShiFencms/LuoShiFencms/` 下有一整套 **Laravel 默认的前端构建脚手架从未被使用**：

- 全部视图的 CSS/JS 走 CDN（bootcdn 的 bootstrap / jquery）与内联 `<style>` / `<script>`；
- 全仓库对 `mix()` / `elixir()` / `app.css` / `app.js` / `laravel-mix` 的引用数为 **0**；
- `resources/js/components/ExampleComponent.vue` 是 Laravel 自带的示例组件。

因此移除了 `package.json`、`webpack.mix.js`、`resources/js/`、`resources/sass/`
及对应的编译产物 `public/js/app.js`、`public/css/app.css`，
**一次性清掉了该 `package.json` 报告的 20 条 axios 告警**（原 112 → 92）。

## 风险评估与处置

本项目是**个人学习作品**，不对外提供服务、不处理真实用户数据、未部署到公网。
上述告警的实际风险面有限 —— 例如 `phpspreadsheet` 的多数公告与不可信 XLSX 文件解析相关，
本项目的 Excel 导出为服务端生成，不接收用户上传的表格。

**处置方式**：92 条全部标记为 *ignored*。这不是「无风险」的结论，
而是「在当前项目定位下选择不修复，并记录原因」。

> **如果你要基于本仓库对外部署**，请在部署前完成 Laravel 主版本升级，
> 或自行评估这些告警在你部署形态下的实际可达性。

## 已实施的安全措施

- **密码存储** — 用户与收货单位密码全部经 `bcrypt` 哈希；演示账号密码不硬编码在代码里，
  由 seeder 通过 `DemoPassword::get()` 读取 `.env` 的 `SEED_PASSWORD`，
  未配置时直接报错而非静默使用空密码。
- **角色隔离** — `CheckRole` 中间件按路由校验 `super_admin` / `company_admin` /
  `dispatcher` 三类角色；企业管理员的操作范围限定在自身 `enterprise_id` 之内。
- **签收防重复** — `sign_records.dispatch_record_id` 设唯一索引，
  一张发货单只能签收一次，二维码被转发也无法重复提交。
- **文件路径不可控** — 发货照片与二维码的文件名由系统生成为 `{发货单ID}.png`，
  不接受用户传入路径，无路径穿越面。
- **第三方凭据外置** — 百度 OCR 的 `BAIDU_OCR_APP_ID` / `API_KEY` / `SECRET_KEY`
  全部走环境变量，代码中不含明文凭据；未配置时 OCR 接口直接返回未配置，不降级放行。
- **CSRF** — 沿用 Laravel 默认的 `VerifyCsrfToken` 中间件保护全部写操作表单。
- **数据库** — 数据访问经 Eloquent / Query Builder 的参数绑定，无字符串拼接 SQL。

> 注：`sign_records` 表中的 `data_hash` / `hash_created_at` 两列为预留的防篡改哈希字段，
> 当前业务代码尚未写入（全库为 NULL），不构成一项已启用的安全措施。

## 报告安全问题

本项目为个人作品，如发现问题欢迎直接提 Issue。
