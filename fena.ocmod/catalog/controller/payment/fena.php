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
namespace Opencart\Catalog\Controller\Extension\Fena\Payment;

/**
 * Fena Class Doc Comment
 *
 * @category Class
 * @package  Fena
 * @author   A N Other <support@fena.co>
 * @license  https://www.fena.co General Public License
 * @link     https://www.fena.co/
 */
class Fena extends \Opencart\System\Engine\Controller
{


    /**
     * Index the build template
     *
     * @return void
     */
    public function index(): string
    {

        $extensionFena = 'extension/fena/payment/fena';

        $this->load->language($extensionFena);

        $source = 'extension/fena/catalog/controller/checkout/webhook.php';
        $destination = 'catalog/controller/checkout/webhook.php';
        // Copy directory.
        copy($source, $destination);

        $data['language'] = $this->config->get('config_language');
        $data['payment_fena_title'] = $this->config->get('payment_fena_title');
        $data['payment_fena_description'] = $this->config->get('payment_fena_description');

        return $this->load->view($extensionFena, $data);

    } //end index()


    /**
     * Confirm the build template
     *
     * @return void
     */
    public function confirm(): void
    {
        $this->load->language('extension/fena/payment/fena');

        $json = [];

        if (!isset($this->session->data['order_id'])) {
            $json['error'] = $this->language->get('error_order');
        }

        if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != 'fena') {
            $json['error'] = $this->language->get('error_payment_method');
        }

        if (!$json) {

            $source = 'extension/fena/catalog/controller/checkout/response.php';
            $destination = 'catalog/controller/checkout/response.php';
            // Copy directory.
            copy($source, $destination);

            $this->load->model('checkout/order');
            $orderInfo = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            try {

                $amount = $this->currency->format($orderInfo['total'], $orderInfo['currency_code'], $orderInfo['currency_value'], false);
                $finalAmount = str_replace(',', '', number_format($amount, 2, '.', ''));
                $fenaApi = html_entity_decode($this->config->get('payment_fena_api'), ENT_QUOTES, 'UTF-8');
                $fenaSecret = html_entity_decode($this->config->get('payment_fena_secret'), ENT_QUOTES, 'UTF-8');
                $bankId = html_entity_decode($this->config->get('payment_fena_bank'), ENT_QUOTES, 'UTF-8');
                $fenaCustName = $orderInfo['firstname'];
                $fenaEmail = $orderInfo['email'];
                $addressLine1 = $orderInfo['shipping_address_1'];
                $addressLine2 = $orderInfo['shipping_address_2'];
                $zipCode = $orderInfo['shipping_postcode'];
                $city = $orderInfo['shipping_city'];
                $country = $orderInfo['shipping_country'];
                $fenaMode = $this->config->get('payment_fena_mode');
                $redirectUrl = $this->config->get('config_url') . '/index.php?route=checkout/response&';

                // API URL to send data.
                if ($fenaMode == 1) {
                    $url = 'https://epos.api.staging.fena.co/open/payments/single/create-and-process';
                } else {
                    $url = 'https://epos.api.fena.co/open/payments/single/create-and-process';
                }

                $deliveryAddress = array(
                    'addressLine1' => $addressLine1,
                    'addressLine2' => $addressLine2,
                    'zipCode' => $zipCode,
                    'city' => $city,
                    'country' => $country
                );

                $data = array(
                    "reference" => $this->session->data['order_id'],
                    "amount" => $finalAmount,
                    "bankAccount" => $bankId,
                    "customerName" => $fenaCustName,
                    "customerEmail" => $fenaEmail,
                    "customRedirectUrl" => $redirectUrl,
                    "type" => "link",
                    "deliveryAddress" => $deliveryAddress
                );

                // Data should be passed as json format.
                $dataJson = json_encode($data);

                // Curl initiate.
                $ch = curl_init();
                $curlArray = array(
                    'integration-id: ' . $fenaApi . '',
                    'secret-key: ' . $fenaSecret . '',
                    'Content-Type: application/json',
                );
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $curlArray);

                // SET Method as a POST.
                curl_setopt($ch, CURLOPT_POST, 1);

                // Pass user data in POST command.
                curl_setopt($ch, CURLOPT_POSTFIELDS, $dataJson);

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                // Execute curl and assign returned data.
                $response = curl_exec($ch);

                // Close curl.
                curl_close($ch);

                // See response if data is posted successfully or any error.
                $response = json_encode($response);
                $response = json_decode($response);
                $array = json_decode($response, true);
                $json['redirect'] = $array['result']['link'];

            } catch (Exception $e) {

                $this->log($e->getMessage());

            } //end try

        } //end if

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    } //end confirm()


} //end class
