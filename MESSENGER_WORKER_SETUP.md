# Messenger Worker 设置说明

## 问题描述

当通过HTTP接口触发事件（如 `/app/oldman/account` 触发 `account.register` 事件）时：
- 事件成功分发，返回 `{"success": true, "message": "Event successfully dispatched"}`
- 但关联的webhook（如 `risk.register`）没有执行
- `messenger_messages` 表中新增了记录，但 `delivered_at` 字段为空

**根本原因**：Symfony Messenger Worker 没有运行，导致队列中的消息没有被处理。

## 解决方案

### 1. 运行 Messenger Worker

使用以下命令启动 worker 来消费队列中的消息：

```bash
php bin/fusio messenger:consume
```

### 2. 在后台运行 Worker（推荐用于生产环境）

#### 使用 nohup：
```bash
nohup php bin/fusio messenger:consume > /var/log/fusio-messenger.log 2>&1 &
```

#### 使用 systemd（创建服务文件）：
创建 `/etc/systemd/system/fusio-messenger.service`：

```ini
[Unit]
Description=Fusio Messenger Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/fusio
ExecStart=/usr/bin/php /path/to/fusio/bin/fusio messenger:consume
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

然后启用并启动服务：
```bash
sudo systemctl enable fusio-messenger
sudo systemctl start fusio-messenger
```

#### 使用 Docker Compose：
在 `docker-compose.yml` 中添加一个 worker 服务：

```yaml
services:
  fusio-worker:
    build: .
    command: php bin/fusio messenger:consume
    volumes:
      - .:/var/www/html
    depends_on:
      - db
      - redis  # 如果使用 Redis transport
    restart: unless-stopped
```

### 3. Worker 命令选项

```bash
# 限制处理的消息数量
php bin/fusio messenger:consume --limit=100

# 设置失败消息限制
php bin/fusio messenger:consume --failure-limit=10

# 设置运行时间限制（秒）
php bin/fusio messenger:consume --time-limit=3600

# 设置无消息时的休眠时间（秒）
php bin/fusio messenger:consume --sleep=5

# 查看详细日志
php bin/fusio messenger:consume -vv
```

### 4. 停止 Worker

```bash
# 发送停止信号
php bin/fusio messenger:stop-workers

# 或者使用 Ctrl+C（如果在前台运行）
```

## 验证 Worker 是否正常工作

1. **检查进程**：
   ```bash
   ps aux | grep "messenger:consume"
   ```

2. **检查日志**：
   ```bash
   tail -f /var/log/fusio-messenger.log
   ```

3. **检查数据库**：
   ```sql
   SELECT * FROM messenger_messages 
   WHERE delivered_at IS NOT NULL 
   ORDER BY created_at DESC 
   LIMIT 10;
   ```

4. **触发测试事件**：
   - 发送HTTP请求触发事件
   - 观察 worker 日志
   - 检查 `messenger_messages` 表的 `delivered_at` 字段是否被更新
   - 检查 webhook 是否被调用

## 消息处理流程

1. **事件分发**：`UtilDispatchEvent` → `Dispatcher::dispatch()` → 创建 `TriggerEvent` 消息
2. **消息入队**：消息存储到 `messenger_messages` 表
3. **Worker 消费**：`WebhookSendHandler` 处理 `TriggerEvent`，查找关联的 webhook
4. **发送请求**：`SendHttpRequestHandler` 执行 HTTP 请求到 webhook endpoint
5. **更新状态**：webhook 响应状态更新到 `fusio_webhook_response` 表

## 故障排查

### 消息一直未被处理

1. 确认 worker 正在运行：
   ```bash
   ps aux | grep messenger:consume
   ```

2. 检查 messenger 配置（`.env` 文件中的 `APP_MESSENGER`）：
   ```bash
   # 应该类似：
   APP_MESSENGER=doctrine://default
   # 或
   APP_MESSENGER=redis://localhost:6379/messages
   ```

3. 检查数据库连接是否正常

4. 查看 worker 日志中的错误信息

### Webhook 未被调用

1. 检查事件和 webhook 的关联关系（`fusio_event` 和 `fusio_webhook` 表）
2. 检查 webhook endpoint 是否可访问
3. 查看 `fusio_webhook_response` 表中的错误信息

## 注意事项

- Worker 需要持续运行才能处理队列中的消息
- 如果 worker 停止，新消息会堆积在队列中
- 建议使用进程管理器（如 systemd、supervisor）来确保 worker 自动重启
- 在生产环境中，考虑运行多个 worker 实例以提高处理能力


