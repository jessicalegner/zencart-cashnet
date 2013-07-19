Integrate ZenCart with CashNet
===============

###Files Included:
mysite/includes/modules/payment/cashnet.php
mysite/includes/modules/payment/cashnet/cashnet_admin_notification.php
mysite/includes/languages/english/modules/payment/cashnet.php

###Installation Instructions:
1. Clone or download and extract
2. Back up existing ZenCart directory, just in case ;)
3. Copy files to your ZenCart directory
4. Run MySql script: cashnet.sql
5. Add the following lines to mysite/includes/database_tables.php
`define('TABLE_CASHNET', DB_PREFIX . 'cashnet');
define('TABLE_CASHNET_SESSION', DB_PREFIX . 'cashnet_session');`
6. Modify your CashNet settings in mysite/includes/languages/english/modules/payment/cashnet.php 
7. Sign in to your ZenCart admin
8. To install, click Modules->Payment->CashNet->Install
9. To enable, click edit->true->update
