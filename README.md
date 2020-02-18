# The official Aframark extension for Magento 2

Aframark is an open review platform that helps consumers make better choices while helping companies showcase and improve their customer service.

To install the Trustpilot plugin on your website, please follow the steps provided in this package.

## How to install the Aframark extension
1. Log in to your Magento server using SSH (Secure Shell) and run the commands that follow.
2. Create a system and database backup by navigating to the root directory of your Magento installation and execute this command:
```
php bin/magento setup:backup --code --db --media
```
  (Please note that your website will be inaccessible during the backup process.)


```
npm install
```

### Compiles and hot-reloads for development

```
npm run serve
```

### Compiles and minifies for production

```
npm run build
```

### Lints and fixes files

```
npm run lint
```

### Customize configuration

See [Configuration Reference](https://cli.vuejs.org/config/).
