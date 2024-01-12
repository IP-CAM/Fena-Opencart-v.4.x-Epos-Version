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

namespace Opencart\Admin\Model\Extension\Fena\Payment;

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
     * GetAccessCode the build template
     *
     * @param request $request comment about this variable
     *
     * @return void
     */
    public function getAccessCode($request)
    {
        try {

            if (isset($this->request->post['payment_fena_mode'])) {
                $fenaMode = $this->request->post['payment_fena_mode'];
            } else {
                $fenaMode = $this->config->get('payment_fena_mode');
            }

            if ($fenaMode == 1) {
                $url = 'https://epos.api.staging.fena.co/open/company/bank-accounts/list';
            } else {
                $url = 'https://epos.api.fena.co/open/company/bank-accounts/list';
            }

            $response = $this->sendCurl($url, $request);
            $response = json_decode($response);
            return $response;

        } catch (Exception $e) {

            $this->log($e->getMessage());

            return false;

        } //end try

    } //end getAccessCode()


    /**
     * SendCurl the build template
     *
     * @param url    $url    get url
     * @param data   $data   get data
     * @param isPost $isPost get request
     *
     * @return void
     */
    public function sendCurl($url, $data, $isPost = true)
    {

        if (isset($this->request->post['payment_fena_api'])) {
            $fenaUsername = $this->request->post['payment_fena_api'];
        } else {
            $fenaUsername = $this->config->get('payment_fena_api');
        }

        if (isset($this->request->post['payment_fena_secret'])) {
            $fenaPassword = $this->request->post['payment_fena_secret'];
        } else {
            $fenaPassword = $this->config->get('payment_fena_secret');
        }

        $curlHandle = curl_init($url);
        $arrayHeader = array(
            'integration-id: ' . $fenaUsername . '',
            'secret-key: ' . $fenaPassword . '',
            'Content-Type: application/json',
        );
        // Set the curl URL option.
        // curl_setopt($curl_handle, CURLOPT_URL, $url);
        // This option will return data as a string instead of direct output.
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $arrayHeader);
        curl_setopt($curlHandle, CURLOPT_TIMEOUT, 60);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curlHandle, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($curlHandle, CURLOPT_FRESH_CONNECT, 1);

        $response = curl_exec($curlHandle);

        if (curl_errno($curlHandle) != CURLE_OK) {
            $response = new \stdClass();
            $response->Errors = "POST Error: " . curl_error($curlHandle) . " URL: $url";
            $this->log->write(array('error' => curl_error($curlHandle), 'errno' => curl_errno($curlHandle)), 'cURL failed');
            $response = json_encode($response);
        } else {
            $info = curl_getinfo($curlHandle);
            if ($info['http_code'] != 200) {
                $response = new \stdClass();
                if ($info['http_code'] == 401 || $info['http_code'] == 404 || $info['http_code'] == 403) {
                    $response->Errors = "Please check ID and Secret Key";
                } else {
                    $response->Errors = 'Error connecting to eWAY: ' . $info['http_code'];
                }

                $response = json_encode($response);
            }
        }

        curl_close($curlHandle);

        return $response;

    } //end sendCurl()


} //end class
