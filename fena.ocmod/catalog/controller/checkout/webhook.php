<?php
/**
 * Webhook File Doc Comment
 * php version 7.2.10
 *
 * @category Class
 * @package  Fena
 * @author   A N Other <support@fena.co>
 * @license  https://www.fena.co General Public License
 * @link     https://www.fena.co/
 */
namespace Opencart\Catalog\Controller\Checkout;

/**
 * Webhook Class Doc Comment
 *
 * @category Class
 * @package  Fena
 * @author   A N Other <support@fena.co>
 * @license  https://www.fena.co General Public License
 * @link     https://www.fena.co/
 */
class Webhook extends \Opencart\System\Engine\Controller
{


    /**
     * Index the build template
     *
     * @return void
     */
    public function index()
    {

        $data = json_decode(file_get_contents('php://input'), true);

        // Write log file.
        $this->log->write(print_r($data, true));

        $orderId = $data['reference'];

        $this->load->model('checkout/order');
        $status = $data['status'];

        $message = 'Transaction ID: '.$data['transaction']."\n";

        if ($status == 'paid') {
                //$statusId = 2;
                $statusId = $this->config->get('payment_fena_paid_status_id');
                $this->model_checkout_order->addHistory($orderId, $statusId, $message);
        }

    }//end index()


}//end class
