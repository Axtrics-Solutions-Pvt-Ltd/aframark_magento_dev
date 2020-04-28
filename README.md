# (Sandbox) The official Aframark extension for Magento 2

Please note that this is the sandbox version of the app and if you are looking for the production version then kindly use this link:
https://github.com/axtricssolutions/aframark_magento

To install the Aframark plugin on your website, please follow the steps provided in this package.

## How to install the Aframark extension
1. Log in to your Magento server using SSH (Secure Shell) and run the commands that follow.
2. Create a system and database backup by navigating to the root directory of your Magento installation and execute this command:
```
php bin/magento setup:backup --code --db --media
```
(Please note that your website will be inaccessible during the backup process.)

3. Enable maintenance mode.
```
php bin/magento maintenance:enable
```

4. Download and install the Aframark plugin using Composer.
```
composer require aframark/module-qa-sandbox
```

5. If this is the first time you install a plugin using Composer, Magento will ask you to provide your Magento Marketplace account credentials. To find your account information go to (https://marketplace.magento.com) > My profile > Access Keys > Create A New Access Key. Note: Your public key is your username, while your private key is your password. 

6. Enable the Aframark plugin.
```
php bin/magento module:enable Axtrics_Aframark --clear-static-content
```

7. Update the database schema. (Please proceed cautiously: This command is global and will enable all Magento plugins that you’ ve installed.)
```
php bin/magento setup:upgrade
```

8. Compile (This command is only required in production mode.)
```
php bin/magento setup:di:compile
```

9. Deploy static content (This command is only required in production mode.)
```
php bin/magento setup:static-content:deploy
```

10. Disable maintenance mode.
```
php bin/magento maintenance:disable
```

