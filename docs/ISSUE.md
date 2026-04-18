# Issue：Backend（`/apps/fusio`）Dashboard 仅显示坐标轴/表格标线，无观测曲线

## 现象

通过浏览器访问 Backend应用（路径形如 `/apps/fusio`）时，Dashboard 页面上的图表区域只有坐标轴或网格线，折线/柱状数据曲线不显示；页面其它部分（导航、表格等）可能仍正常。

## 原因说明

### 1. 前端写死的 API 根地址与浏览器实际访问地址不一致（最可能）

Fusio 的 Backend 应用（marketplace 中的 `fusio`）在安装时会改写应用目录下的 `index.html`，把模板里的 `${API_URL}` 等占位符替换为**当时**配置中的对外 API 地址。该逻辑在 `fusio/impl` 的 `Fusio\Impl\Service\Marketplace\App\Installer` 中：`replaceVariables()` 使用 `FrameworkConfig::getDispatchUrl()` 作为 `API_URL`，而 `getDispatchUrl()` 由配置项 `psx_url`（环境变量 **`APP_URL`**）与 `psx_dispatch` 拼接得到。

因此：

- 若 `.env` 中 `APP_URL`（以及配套的 `APP_APPS_URL`）仍是示例值 **`http://localhost:8010`**，而用户实际通过 **局域网 IP**、**其它端口**、**HTTPS** 或 **网关路径前缀**（例如统一入口 `https://example.com/apps/...` 再反代到 Fusio）访问，则已生成的 `public/apps/fusio/index.html` 里内联的 `FUSIO_URL`（与 `API_URL` 同源）仍指向 `localhost` 或错误的 scheme/host/path。
- 浏览器中的 Dashboard 会向该地址请求 Backend API（如 Dashboard 聚合数据）。请求失败（网络错误、CORS、混合内容 blocked、连到错误主机）时，图表库往往仍渲染坐标轴，但**数据序列为空或无法绘制曲线**，表现为「只有表格标线」。

参考：项目内 `configuration.php` 中 `psx_url` 对应 `APP_URL`，`fusio_apps_url` 对应 `APP_APPS_URL`；`.env.example` 中二者需与对外访问方式一致。

### 2. 当前仓库内 nginx 与路径的关系（辅助理解）

参考配置：

- 全局：`/Users/mini/Projects/docker-lab/middleware/conf/nginx/nginx.conf`（`http` 块通过 `include` 加载站点配置）。
- Fusio 站点：`/Users/mini/Projects/docker-lab/middleware/conf/nginx/sites-enabled/fusio.conf`  
  - `listen 8010`；`root /var/www/fusio/public`；`location /` 走 `try_files` 与 PHP。  
  - 该文件**未**定义 `/apps/fusio` 这一前缀；`/apps/fusio` 是 **`public/apps/fusio/`** 下的静态前端资源路径（在 `root` 为 `public` 时，URL 为 `http://<host>:8010/apps/fusio/`）。

若在 Fusio 容器/本机 8010 **之外**还有一层反向代理把某前缀映射到该服务，则浏览器地址栏的 **origin与路径** 必须与 `APP_URL` / `APP_APPS_URL` 一致，否则仍会触发第 1 条问题。

### 3. 其它需排除的情况（次要）

- **确实无访问日志数据**：Dashboard 中部分序列来自 `fusio_log` 等表的统计（见 `Backend\View\Statistic\IncomingRequests` 等）。若实例新建后几乎无 API 流量，数值可能全为 0；曲线可能贴近0 轴或与「无数据」表现接近。可与 Network 面板中 Dashboard API 是否 200、响应体是否含非零 `data` 区分。
- **Fusio 版本**：上游发行说明曾提到 Dashboard 响应中 `null` 与图表展示相关的问题，若版本过旧可对照 [Fusio 更新说明](https://www.fusio-project.org/blog) 考虑升级。此条需结合当前 `composer` 锁定版本单独验证。

## 修改方案（不改业务代码的前提下）

1. **将环境变量改为浏览器实际使用的对外地址**  
   - 在运行 Fusio 的环境（如 Docker Compose / `.env`）中设置：  
     - `APP_URL`：用户访问 **API（`public` 根）** 时使用的完整基准 URL（协议 + 主机 + 端口 + 如需的路径前缀）。  
     - `APP_APPS_URL`：用户访问 **apps 目录** 的完整基准 URL，一般为 `APP_URL` + `/apps`（若对外路径不同则按网关实际路由填写）。  
   - 避免在「从其它机器访问」的场景下仍使用 `localhost`。

2. **重新生成 Backend 应用内嵌的 `API_URL`**  
   - 修改 `.env` 后，需要让 `public/apps/fusio/index.html` 再次执行安装时的占位符替换（与 `php bin/fusio marketplace:install` 安装 Backend 应用时相同的效果）。可按运维习惯选择：在控制台对 `fusio` 应用执行 **marketplace 更新/重装**（以项目文档 `bin/fusio` 子命令为准），或按官方文档在更新配置后刷新应用环境变量；**切勿**只改 `.env` 却保留旧的 `index.html` 内联地址。

3. **校验**  
   - 浏览器开发者工具 **Network**：打开 Dashboard 时，确认对 Backend 的请求发往**当前页面的同一站点**（或预期的 API 域名），状态码 200，响应 JSON 中图表相关字段有 `labels` / `data`。  
   - 若经 HTTPS页面请求 HTTP API，需注意浏览器混合内容策略。

4. **若存在上层反向代理**  
   - 除修正 `APP_URL` / `APP_APPS_URL` 外，确保代理转发 `Host`、`X-Forwarded-Proto`、`X-Forwarded-Prefix`（若使用路径前缀）等与 Fusio/文档要求一致，避免服务端生成的链接与浏览器不一致。nginx 站点配置是否需补充 `proxy_set_header`，由**上一层**入口 nginx 决定；当前 `fusio.conf` 仅覆盖8010 直连场景。

按上述步骤使 **内联 `FUSIO_URL` / `API_URL` 与浏览器访问 Fusio 的公共 URL 一致** 后，Dashboard 观测曲线应能正常绘制（在确有日志数据的前提下）。
