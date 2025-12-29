

## create user

### admin

```shell
php bin/fusio adduser -r 1 -u admin -e rongjin.zh@gmail.com --category default
```
r=1: Administrater

### backend

Enter the username: developer
Enter the email: backend.developer@zhang.lab
Enter the password:



## install applications

```shell
php bin/fusio marketplace:install fusio
```


## create service-scanning database file

```shell
mkdir /run/shell
touch /run/shell/uri_ip.csv
```
