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

namespace Opencart\Admin\Controller\Extension\Fena\Payment;

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
    public function index(): void
    {
        $extensionFena = 'extension/fena/payment/fena';
        $this->load->language($extensionFena);

        $this->document->setTitle($this->language->get('heading_title'));
        $data['breadcrumbs'] = [];

        $userToken = 'user_token=';

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $userToken . $this->session->data['user_token']),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', $userToken . $this->session->data['user_token'] . '&type=payment'),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($extensionFena, $userToken . $this->session->data['user_token']),
        ];

        if (isset($this->request->post['payment_fena_mode'])) {
            $data['payment_fena_mode'] = $this->request->post['payment_fena_mode'];
        } else {
            $data['payment_fena_mode'] = $this->config->get('payment_fena_mode');
        }

        if (isset($this->request->post['payment_fena_api'])) {
            $data['payment_fena_api'] = $this->request->post['payment_fena_api'];
        } else {
            $data['payment_fena_api'] = $this->config->get('payment_fena_api');
        }

        if (isset($this->request->post['payment_fena_secret'])) {
            $data['payment_fena_secret'] = $this->request->post['payment_fena_secret'];
        } else {
            $data['payment_fena_secret'] = $this->config->get('payment_fena_secret');
        }

        if (isset($this->request->post['payment_fena_status'])) {
            $data['payment_fena_status'] = $this->request->post['payment_fena_status'];
        } else {
            $data['payment_fena_status'] = $this->config->get('payment_fena_status');
        }

        if (isset($this->request->post['payment_fena_title'])) {
            $data['payment_fena_title'] = $this->request->post['payment_fena_title'];
        } else {
            $data['payment_fena_title'] = $this->config->get('payment_fena_title');
        }

        if (isset($this->request->post['payment_fena_description'])) {
            $data['payment_fena_description'] = $this->request->post['payment_fena_description'];
        } else {
            $data['payment_fena_description'] = $this->config->get('payment_fena_description');
        }

        if (isset($this->request->post['payment_fena_paid_status_id'])) {
            $data['payment_fena_paid_status_id'] = $this->request->post['payment_fena_paid_status_id'];
        } else {
            $data['payment_fena_paid_status_id'] = $this->config->get('payment_fena_paid_status_id');
        }

        if (isset($this->request->post['payment_fena_rejected_status_id'])) {
            $data['payment_fena_rejected_status_id'] = $this->request->post['payment_fena_rejected_status_id'];
        } else {
            $data['payment_fena_rejected_status_id'] = $this->config->get('payment_fena_rejected_status_id');
        }

        if (isset($this->request->post['payment_fena_refunded_status_id'])) {
            $data['payment_fena_refunded_status_id'] = $this->request->post['payment_fena_refunded_status_id'];
        } else {
            $data['payment_fena_refunded_status_id'] = $this->config->get('payment_fena_refunded_status_id');
        }
        if (isset($this->request->post['payment_fena_bank'])) {
            $data['payment_fena_bank'] = $this->request->post['payment_fena_bank'];
        } else {
            $data['payment_fena_bank'] = $this->config->get('payment_fena_bank');
        }

        if (isset($this->request->post['payment_fena_bank_id'])) {
            $data['payment_fena_bank_id'] = $this->request->post['payment_fena_bank_id'];
        } else {
            $data['payment_fena_bank_id'] = $this->config->get('payment_fena_bank_id');
        }
        if (isset($this->request->post['payment_fena_bank_name'])) {
            $data['payment_fena_bank_name'] = $this->request->post['payment_fena_bank_name'];
        } else {
            $data['payment_fena_bank_name'] = $this->config->get('payment_fena_bank_name');
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['redirect_url'] = HTTP_CATALOG . 'index.php?route=checkout/response';
        $data['notification_url'] = HTTP_CATALOG . 'index.php?route=checkout/waybook';
        $data['save'] = $this->url->link($extensionFena . '|save', $userToken . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', $userToken . $this->session->data['user_token'] . '&type=payment');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($extensionFena, $data));

    } //end index()


    /**
     * Save the build template
     *
     * @return void
     **/
    public function save(): void
    {
        $extensionFena = 'extension/fena/payment/fena';
        $this->load->language($extensionFena);

        $json = [];
        if (!$this->user->hasPermission('modify', $extensionFena)) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {

            $this->load->model($extensionFena);
            $result = $this->model_extension_fena_payment_fena->getAccessCode($this->request->post);
            // Check if any error returns.
            if (isset($result->Errors)) {
                $errorArray = explode(",", $result->Errors);
                $lblError = "";
                foreach ($errorArray as $error) {
                    $error = $this->language->get($error);
                    $lblError .= $error . "<br />\n";
                }

                $this->log->write('fena Payment error: ' . $lblError);
            }

            if (isset($lblError)) {
                $json['error'] = $lblError;
            } else {
                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('payment_fena', $this->request->post);
                $json['success'] = $this->language->get('text_success');
            }

        } //end if

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    } //end save()


} //end class
