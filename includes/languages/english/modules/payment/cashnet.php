<?php
/**
 * @package languageDefines
 */
  
  define('MODULE_PAYMENT_CASHNET_CONFIG_URL', '[YOUR_CASHNET_URL]');
  define('MODULE_PAYMENT_CASHNET_CONFIG_KEY', '[YOUR_CASHNET_CONFIG_KEY]');
  define('MODULE_PAYMENT_CASHNET_CONFIG_ITEMCODE', '[YOUR_CASHNET_ITEM_CODE]');


  define('MODULE_PAYMENT_CASHNET_TEXT_ADMIN_TITLE', 'CashNet');
  define('MODULE_PAYMENT_CASHNET_TEXT_ADMIN_TITLE_NONUSA', 'CashNet');
  define('MODULE_PAYMENT_CASHNET_TEXT_CATALOG_TITLE', 'CashNet');
  if (IS_ADMIN_FLAG === true) {
  define('MODULE_PAYMENT_CASHNET_TEXT_DESCRIPTION', '<strong>CashNet Payment Module</strong>');
 } else {
    define('MODULE_PAYMENT_CASHNET_TEXT_DESCRIPTION', '<strong>CashNet</strong>');
  }

  define('MODULE_PAYMENT_CASHNET_TEXT', 'You will be securely transferred to CashNet where you can make your payment. We will not store any credit card information.');
  define('MODULE_PAYMENT_CASHNET_TEXT_CATALOG', 'Credit Card <br >' .
                                                    '<span class="smallText">' . MODULE_PAYMENT_CASHNET_TEXT . '</span>');


  define('MODULE_PAYMENT_CASHNET_ENTRY_FIRST_NAME', 'First Name:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_LAST_NAME', 'Last Name:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_BUSINESS_NAME', 'Business Name:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_STREET', 'Address Street:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_CITY', 'Address City:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_STATE', 'Address State:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_ZIP', 'Address Zip:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_COUNTRY', 'Address Country:');
  define('MODULE_PAYMENT_CASHNET_ENTRY_EMAIL_ADDRESS', 'Email:');

  define('MODULE_PAYMENT_CASHNET_PURCHASE_DESCRIPTION_TITLE', 'All the items in your shopping basket (see details in the store and on your store receipt).');
  define('MODULE_PAYMENT_CASHNET_PURCHASE_DESCRIPTION_ITEMNUM', STORE_NAME . ' Purchase');
  define('MODULES_PAYMENT_CASHNETSTD_LINEITEM_TEXT_ONETIME_CHARGES_PREFIX', 'One-Time Charges related to ');
  define('MODULES_PAYMENT_CASHNETSTD_LINEITEM_TEXT_SURCHARGES_SHORT', 'Surcharges');
  define('MODULES_PAYMENT_CASHNETSTD_LINEITEM_TEXT_SURCHARGES_LONG', 'Handling charges and other applicable fees');
  define('MODULES_PAYMENT_CASHNETSTD_LINEITEM_TEXT_DISCOUNTS_SHORT', 'Discounts');
  define('MODULES_PAYMENT_CASHNETSTD_LINEITEM_TEXT_DISCOUNTS_LONG', 'Credits applied, including discount coupons, gift certificates, etc');
