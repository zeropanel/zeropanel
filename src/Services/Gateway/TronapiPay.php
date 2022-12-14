<?php


namespace App\Services\Gateway;

use App\Models\Setting;
use App\Controllers\OrderController;
use Slim\Http\Request;
use Slim\Http\Response;

class TronapiPay
{
    public function __construct()
    {
        $configs = Setting::getClass('tronapipay');
        $this->public_key = $configs['tronapipay_public_key'];
        $this->private_key = $configs['tronapipay_private_key'];
        $this->gatewayUri = 'https://pro.tronapi.com/api/transaction/create';
    }

    /**
     * @name    ηζη­Ύε
     * @param sourceData
     */
    public function sign($data)
    {
        return strtolower(md5($data));
    }

    public function post($data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->gatewayUri);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * @param $type
     * @param $price
     * @param $buyshop
     * @param int $order_id
     */
    public function ZeroPay($user_id, $method, $order_no, $amount)
    {
        $currency = Setting::getClass('currency');
        
        if ($currency['enable_currency'] == true && $currency['currency_exchange_rate'] != null) {
            $final_amount = $amount * $currency['currency_exchange_rate'];
        } else {
            $final_amount = $amount;
        }

        $amount = $final_amount;
        $currency = 'CNY';
        $coin_code = 'USDT';
        $order_id = $order_no;
        $customer_id = $user_id;
        $product_name = '';
        $notify_url = Setting::obtain('website_general_url') . '/payment/notify/tronapipay';
        $redirect_url = Setting::obtain('website_general_url') . '/user/payment/return?tradeno=' . $order_no;
        $signatureStr = $amount.$currency.$coin_code.$order_id.$product_name.$customer_id.$notify_url.$redirect_url.$this->public_key.$this->private_key;
        $signature = $this->sign($signatureStr);

        $data = [
            'amount' => $amount,
            'currency' => $currency,
            'coin_code' => $coin_code,
            'order_id' => $order_id,
            'customer_id' => $customer_id,
            'product_name' => $product_name,
            'notify_url' => $notify_url,
            'redirect_url' => $redirect_url,
            'public_key' => $this->public_key,
            'signature' => $signature,
        ];

        $result = json_decode($this->post($data), true);
        if (!isset($result['data']['cashier_url'])) {        
            return ['ret' => 0, 'msg' => 'ζ―δ»η½ε³ε€ηε€±θ΄₯'];
        }
        return ['ret' => 1, 'url' => $result['data']['cashier_url'], 'type' => 'url'];
    }

    public function notify($request, $response, $args)
    {
        //file_put_contents(BASE_PATH . '/storage/paytaro.log', json_encode($request->getParams())."\r\n", FILE_APPEND);
    	$transaction_token = $request->getParam('transaction_token');
        $order_id = $request->getParam('order_id');
        $amount = $request->getParam('amount');
        $currency = $request->getParam('currency');
        $coin_code = $request->getParam('coin_code');
        $coin_amount = $request->getParam('coin_amount');
        $hash = $request->getParam('hash');
        $private_key = $this->private_key;
        $signature = $request->getParam('signature');
        $_signatureStr = $transaction_token.$order_id.$amount.$currency.$coin_code.$coin_amount.$hash.$private_key;
        $_signature = $this->sign($_signatureStr);
        if ($_signature != $signature) {
            die('FAIL');
        }
    	OrderController::execute($request->getParam('out_trade_no'));
        $res = [
            'code' => '200',
            'data' => 'ok'
        ];
    	die(json_encode($res));  
    }
}