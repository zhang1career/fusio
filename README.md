<p align="center">
    <a href="https://www.fusio-project.org/" target="_blank"><img src="https://www.fusio-project.org/img/fusio_64px.png"></a>
</p>

# Fusio

This repository is a fork of [Fusio](https://www.fusio-project.org/), an open source API management platform that helps you build, manage, and deploy APIs quickly and efficiently.

What is new in this fork:
- Service discovery integration with a simple service registry
- Customized UtilChain (in fusio/adapter-util) to support staged and cooperative actions
- Enhanced UtilDispatchEvent (in fusio/adapter-util) to patch authorization, request id, and event name into webhook request headers


# Quick Start

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


## create service-scanning database file (DEPRECATED)

```shell
mkdir /run/shell
touch /run/shell/uri_ip.csv
```

## setup messenger worker
refer to [MESSENGER_WORKER_SETUP](./MESSENGER_WORKER_SETUP.md)


---

### 📣 Promotion & Media

Are you a blogger, writer, or run a developer-focused publication? We'd love for you to cover Fusio!

Visit the [Media Page](https://www.fusio-project.org/media) to download official icons for use in your articles or videos.

---

### 🧑‍🏫 Consulting & Workshops

For companies or freelancers who want in-depth guidance on using and integrating Fusio:

- We offer **consulting services** to help you evaluate whether Fusio fits your architecture.
- Our **workshops** walk you through key functionality, answer your specific questions, and help identify the best integration approach.

Feel free to [contact us](https://www.fusio-project.org/contact) for more details.

---

### 💖 Support Fusio

If Fusio helps you build APIs faster or adds value to your projects, please consider supporting our work:

- ⭐ Star the project on GitHub
- ☕ [Sponsor via GitHub](https://github.com/sponsors/chriskapp)
- 💬 Spread the word on social media or write about Fusio

Every bit of support helps us continue improving the platform!

---

### 🤝 Project Partners

We’re grateful to our partners who support the Fusio project and share our vision of advancing open API development.

If your company is interested in becoming a partner and being listed here, consider [becoming a sponsor](https://github.com/sponsors/chriskapp).

<a href="https://jb.gg/OpenSource">
 <picture>
   <source media="(prefers-color-scheme: dark)" srcset="https://www.jetbrains.com/company/brand/img/logo_jb_dos_3.svg">
   <source media="(prefers-color-scheme: light)" srcset="https://resources.jetbrains.com/storage/products/company/brand/logos/jetbrains.svg">
   <img alt="JetBrains logo." src="https://resources.jetbrains.com/storage/products/company/brand/logos/jetbrains.svg">
 </picture>
</a>