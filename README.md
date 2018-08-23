# magento2-console-command
Magento2 sample console command for getting product info
# Installation
## via composer
Run the following command in Magento 2 root folder:
```
composer require ruchlewicz/magento2-console-command
php bin/magento setup:upgrade
```
## manual
Download the repository and put the code into
```
app/code/Ruchlewicz/ConsoleCommand
```
# Usage
Open console and run following command
```
php bin/magento ruchlewicz:product-info <product-id> -a <attribute-code>
```
* product-id - id of the product you want to check
* attribute-code(optional) - attribute code that you want to check, if not provided then all attributes will be displayed


