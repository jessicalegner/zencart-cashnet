<?php
/**
 * admin subtemplate for CashNet payments
 *
 * @package paymentMethod
 */

// strip slashes in case they were added to handle apostrophes:
  foreach ($result->fields as $key=>$value){
    $result->fields[$key] = stripslashes($value);
  }

// display all cashnet status fields (in admin Orders page):
          $output = '<td><table>'."\n";
          $output .= '<tr style="background-color : #cccccc; border-style : dotted;">'."\n";

          $output .= '<td valign="top"><table>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_FIRST_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['first_name']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_LAST_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['last_name']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_BUSINESS_NAME."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= ($result->fields['payer_business_name'] != '') ? $result->fields['payer_business_name'] : "N/A"."\n";
          $output .= '</td></tr>'."\n";

          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_STREET."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['address_street']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_CITY."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['address_city']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_STATE."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['address_state']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_ADDRESS_COUNTRY."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['address_country']."\n";
          $output .= '</td></tr>'."\n";
          $output .= '<tr><td class="main">'."\n";
          $output .= MODULE_PAYMENT_CASHNET_ENTRY_EMAIL_ADDRESS."\n";
          $output .= '</td><td class="main">'."\n";
          $output .= $result->fields['email']."\n";
          $output .= '</td></tr>'."\n";

          $output .= '</table></td>'."\n";

          $output .= '</tr>'."\n";
          $output .='</table></td>'."\n";

