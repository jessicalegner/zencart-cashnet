<?php
/**
 *
 * @package paymentMethod
 */

define('MODULE_PAYMENT_CASHNET_TAX_OVERRIDE', 'true');

/**
 * cashnet payment module class for CashNet method
 *
 */
class cashnet extends base {
  /**
   * string representing the payment method
   * @var string
   */
  var $code;

  /**
   * $title is the displayed name for this payment method
   * @var string
    */
  var $title;

  /**
   * $description is a soft name for this payment method
   * @var string
    */
  var $description;

  /**
   * $enabled determines whether this module shows or not... in catalog.
   * @var boolean
    */
  var $enabled;

  /**
    * constructor
    * @param int $id
    * @return cashnet
    */
  function cashnet($id = '') {
    global $order, $messageStack;

    $this->code = 'cashnet';

    if (IS_ADMIN_FLAG === true) { // Payment Module title in Admin
      $this->title = STORE_COUNTRY != '223' ? MODULE_PAYMENT_CASHNET_TEXT_ADMIN_TITLE_NONUSA : MODULE_PAYMENT_CASHNET_TEXT_ADMIN_TITLE;
      if (IS_ADMIN_FLAG === true && MODULE_PAYMENT_CASHNET_TESTING == 'Test') $this->title .= '<span class="alert"> (dev/test mode active)</span>';
    } else { // Payment Module title in Catalog
      $this->title = MODULE_PAYMENT_CASHNET_TEXT_CATALOG_TITLE;
    }

    $this->description = MODULE_PAYMENT_CASHNET_TEXT_DESCRIPTION;
    $this->sort_order = MODULE_PAYMENT_CASHNET_SORT_ORDER;
    $this->enabled = ((MODULE_PAYMENT_CASHNET_STATUS == 'True') ? true : false);

    if ((int)MODULE_PAYMENT_CASHNET_ORDER_STATUS_ID > 0) {
      $this->order_status = MODULE_PAYMENT_CASHNET_ORDER_STATUS_ID;
    }

    if (is_object($order)) $this->update_status();

    $this->form_action_url = MODULE_PAYMENT_CASHNET_CONFIG_URL;
  }

  /**
   * calculate zone matches and flag settings to determine whether this module should display to customers or not
    *
    */
  function update_status() {
    global $order, $db;

    if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_CASHNET_ZONE > 0) ) {
      $check_flag = false;
      $check_query = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_CASHNET_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
      while (!$check_query->EOF) {
        if ($check_query->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check_query->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check_query->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }

  /**
   * JS validation which does error-checking of data-entry if this module is selected for use
   * (Number, Owner, and CVV Lengths)
   *
   * @return string
    */
  function javascript_validation() {
    return false;
  }

  /**
   * Displays payment method name along with Credit Card Information Submission Fields (if any) on the Checkout Payment Page
   *
   * @return array
    */
  function selection() {
    return array('id' => $this->code,'module' => MODULE_PAYMENT_CASHNET_TEXT_CATALOG);
  }

  /**
   * Normally evaluates the Credit Card Type for acceptance and the validity of the Credit Card Number & Expiration Date
   * Since CashNet module is not collecting info, it simply skips this step.
   *
   * @return boolean
   */
  function pre_confirmation_check() {
    return false;
  }

  /**
   * Display Credit Card Information on the Checkout Confirmation Page
   * Since none is collected for CashNet before forwarding to CashNet site, this is skipped
   *
   * @return boolean
    */
  function confirmation() {
    return false;
  }

  /**
   * Build the data and actions to process when the "Submit" button is pressed on the order-confirmation screen.
   * This sends the data to the payment gateway for processing.
   * (These are hidden fields on the checkout confirmation page)
   *
   * @return string
    */
  function process_button() {
    global $db, $order, $currencies, $currency;
    $options = array();

    $_SESSION['cn_key_to_remove'] = session_id();
    $db->Execute("delete from " . TABLE_CASHNET_SESSION . " where session_id = '" . zen_db_input($_SESSION['cn_key_to_remove']) . "'");

    $sql = "insert into " . TABLE_CASHNET_SESSION . " (session_id, saved_session, expiry) values (
          '" . zen_db_input($_SESSION['cn_key_to_remove']) . "',
          '" . base64_encode(serialize($_SESSION)) . "',
          '" . (time() + (1*60*60*24*2)) . "')";

    $db->Execute($sql);

    $key = MODULE_PAYMENT_CASHNET_CONFIG_KEY;
    $itemcode = MODULE_PAYMENT_CASHNET_CONFIG_ITEMCODE;

    $this->transaction_currency = $_SESSION['currency'];
    $this->totalsum = $order->info['total'] = number_format($order->info['total'], 2);
    $this->transaction_amount = zen_round($this->totalsum * $currencies->get_value($my_currency), $currencies->get_decimal_places($my_currency));
   
    $options = array(
        'itemcode' => $itemcode,
        'amount' => $this->totalsum,
        'digest' => md5(trim($key) . $this->totalsum),
        'custcode' => $order->customer['id'],
        'email' => trim($order->customer['email_address']),
        'acctname' => trim($order->customer['firstname'] . ' ' . $order->customer['lastname']),
        'addr' => trim($order->customer['street_address']),
        'city' => trim($order->customer['city']),
        'state' => trim($order->customer['state']),
        'zip' => trim($order->customer['postcode']),
        'signouturl' => zen_href_link(FILENAME_CHECKOUT_PROCESS, 'referer=cashnet', 'SSL'),
    );

    // build the button fields
    foreach ($options as $name => $value) {
      // remove quotation marks
      $value = str_replace('"', '', $value);
      // check for invalid chars
      if (preg_match('/[^a-zA-Z_0-9]/', $name)) {
        ipn_debug_email('datacheck - ABORTING - preg_match found invalid submission key: ' . $name . ' (' . $value . ')');
        break;
      }

      $buttonArray[] = zen_draw_hidden_field($name, $value);
    }

    $process_button_string = "\n" . implode("\n", $buttonArray) . "\n";

    $_SESSION['cashnet_transaction_info'] = array($this->transaction_amount, $this->transaction_currency);

    return $process_button_string;
  }

  /**
   * Store transaction info to the order and process any results that come back from CashNet
   */
  function before_process() {
    global $order_total_modules, $db;

    list($this->transaction_amount, $this->transaction_currency) = $_SESSION['cashnet_transaction_info'];
    unset($_SESSION['cashnet_transaction_info']);

    if (isset($_GET['referer']) && $_GET['referer'] == 'cashnet' && isset($_POST['tx']) && $_POST['tx'] != '') {
      $this->notify('NOTIFY_PAYMENT_CASHNET_RETURN_TO_STORE');

      // transaction was success so delete from CashNet session table -- housekeeping.
      $db->Execute("delete from " . TABLE_CASHNET_SESSION . " where session_id = '" . zen_db_input($_SESSION['cn_key_to_remove']) . "'");
      unset($_SESSION['cn_key_to_remove']);
      $_SESSION['cashnet_transaction_passed'] = true;

      return true;
    } else {
      $_SESSION['cart']->reset(true);
        unset($_SESSION['sendto']);
        unset($_SESSION['billto']);
        unset($_SESSION['shipping']);
        unset($_SESSION['payment']);
        unset($_SESSION['comments']);
        unset($_SESSION['cot_gv']);
        $order_total_modules->clear_posts();

        $this->notify('NOTIFY_PAYMENT_CASHNET_CANCELLED_DURING_CHECKOUT');
        zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }
  }

  /**
    * Checks referrer
    *
    * @param string $zf_domain
    * @return boolean
    */
  function check_referrer($zf_domain) {
    return true;
  }

  /**
    * Build admin-page components
    *
    * @param int $zf_order_id
    * @return string
    */
  function admin_notification($zf_order_id) {
    global $db;

    $output = '';
    $sql = "select * from " . TABLE_CASHNET . " where order_id = " . (int)$zf_order_id;
    $result = $db->Execute($sql);

    if ($result->RecordCount() > 0 && file_exists(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/cashnet/cashnet_admin_notification.php')) {
      require(DIR_FS_CATALOG . DIR_WS_MODULES . 'payment/cashnet/cashnet_admin_notification.php');
    }
    
    return $output;
  }

  /**
   * Post-processing activities
   *
   * @return boolean
    */
  function after_process() {
    global $insert_id, $db, $order;
 
      $sql_data_array= array(array('fieldName'=>'orders_id', 'value'=>$insert_id, 'type'=>'integer'),
                             array('fieldName'=>'orders_status_id', 'value'=>$this->order_status, 'type'=>'integer'),
                             array('fieldName'=>'date_added', 'value'=>'now()', 'type'=>'noquotestring'),
                             array('fieldName'=>'customer_notified', 'value'=>0, 'type'=>'integer')
                             );
      $db->perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

      $sql_data_array = array(
                        'order_id' => $insert_id,
                        'first_name' => $order->billing['firstname'],
                        'last_name' => $order->billing['lastname'],
                        'payer_business_name' => $order->billing['company'],
                        'address_street' => $order->billing['street_address'],
                        'address_city' => $order->billing['city'],
                        'address_state' => $order->billing['state'],
                        'address_zip' => $order->billing['postcode'],
                        'address_country' => $order->customer['country'],
                        'email' => $order->customer['email_address'],
                        'date_added' => 'now()',
                        'cnResult' => $_POST['result'],
                        'cnRespMessage' => $_POST['respmessage'],
                        'cnBatchNo' => $_POST['batchno'],
                        'cnTx' => $_POST['tx'],
                        'cnAmount' => $_POST['amount1'],
                        'cnPmtType' => $_POST['pmttype'],
                       );

      zen_db_perform(TABLE_CASHNET, $sql_data_array);
  }

  /**
   * Used to display error message details
   *
   * @return boolean
    */
  function output_error() {
    return false;
  }

  /**
   * Check to see whether module is installed
   *
   * @return boolean
    */
  function check() {
    global $db;
    if (IS_ADMIN_FLAG === true) {
      global $sniffer;
      if ($sniffer->field_exists(TABLE_CASHNET, 'zen_order_id'))  $db->Execute("ALTER TABLE " . TABLE_CASHNET . " CHANGE COLUMN zen_order_id order_id int(11) NOT NULL default '0'");
    }

    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_CASHNET_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  /**
   * Install the payment module and its configuration settings
    *
    */
  function install() {
    global $db, $messageStack;
    if (defined('MODULE_PAYMENT_CASHNET_STATUS')) {
      $messageStack->add_session('CashNet module already installed.', 'error');
      zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=cashnet', 'NONSSL'));
      return 'failed';
    }

    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Cashnet Module', 'MODULE_PAYMENT_CASHNET_STATUS', 'True', 'Do you want to accept CashNet payments?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_CASHNET_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '8', now())");
    $this->notify('NOTIFY_PAYMENT_CASHNET_INSTALLED');
  }

  /**
   * Remove the module and all its settings
    *
    */
  function remove() {
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key LIKE 'MODULE\_PAYMENT\_CASHNET\_%'");
    $this->notify('NOTIFY_PAYMENT_CASHNET_UNINSTALLED');
  }

  /**
   * Internal list of configuration keys used for configuration of the module
   *
   * @return array
    */
  function keys() {
    $keys_list = array(
                       'MODULE_PAYMENT_CASHNET_STATUS',
                       'MODULE_PAYMENT_CASHNET_SORT_ORDER'
                        );

    // CashNet testing/debug options go here:
    if (IS_ADMIN_FLAG === true) {
      if (isset($_GET['debug']) && $_GET['debug']=='on') {
        $keys_list[]='MODULE_PAYMENT_CASHNET_DEBUG_EMAIL_ADDRESS';  /* this defaults to store-owner-email-address */
      }
    }
    return $keys_list;
  }
}
