<?php
/**
 * Fena File Doc Comment
 * php version 7.2.10
 *
 * @category Class
 * @package  Fena
 * @author   A N Other <support@fena.co>
 * @license  https://www.fena.co General Public License
 * @link     https://www.fena.co/
 */
namespace Opencart\Catalog\Model\Extension\Fena\Payment;

/**
 * Fena Class Doc Comment
 *
 * @category Class
 * @package  Fena
 * @author   A N Other <support@fena.co>
 * @license  https://www.fena.co General Public License
 * @link     https://www.fena.co/
 */
class Fena extends \Opencart\System\Engine\Model
{


    /**
     * GetMethod the build template
     *
     * @param address $address comment about this variable
     *
     * @return void
     */
    public function getMethod(array $address): array
    {
        $this->load->language('extension/fena/payment/fena');

        $query = $this->db->query("SELECT * FROM `".DB_PREFIX."zone_to_geo_zone` WHERE `geo_zone_id` = '".(int) $this->config->get('payment_cod_geo_zone_id')."' AND `country_id` = '".(int) $address['country_id']."' AND (`zone_id` = '".(int) $address['zone_id']."' OR `zone_id` = '0')");

        if ($this->cart->hasSubscription()) {
            $status = false;
        } elseif (!$this->cart->hasShipping()) {
            $status = false;
        } elseif (!$this->config->get('payment_cod_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }

        $methodData = [];

        if ($status) {
            $methodData = [
                'code'       => 'fena',
                'title'      => $this->language->get('heading_title'),
                'sort_order' => $this->config->get('payment_cod_sort_order'),
            ];
        }

        return $methodData;

    }//end getMethod()


}//end class
