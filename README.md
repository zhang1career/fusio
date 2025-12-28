
## repair data

change default tenant_id from `null` to `'default'`

```sql
UPDATE fusio_category
SET tenant_id = "default";

UPDATE fusio_config
SET tenant_id = "default";

UPDATE fusio_connection
SET tenant_id = "default";

UPDATE fusio_cronjob
SET tenant_id = "default";

UPDATE fusio_event
SET tenant_id = "default";

UPDATE fusio_operation
SET tenant_id = "default";

UPDATE fusio_page
SET tenant_id = "default";

UPDATE fusio_rate
SET tenant_id = "default";

UPDATE fusio_role
SET tenant_id = "default";

UPDATE fusio_schema
SET tenant_id = "default";

UPDATE fusio_scope
SET tenant_id = "default";

UPDATE fusio_token
SET tenant_id = "default";

UPDATE fusio_user
SET tenant_id = "default";
```



## create user

### admin

```shell
php bin/fusio adduser -r 1 -u admin -e rongjin.zh@gmail.com --category default
```
r=1: Administrater

### producer(backend)

```
php bin/fusio adduser -r 2 -u service_foundation_producer -e producer.service_foundation@zhang.lab --category backend
```

r=2: Backend
