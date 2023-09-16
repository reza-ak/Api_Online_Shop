<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends ApiController
{
    public function send()
    {
        $api = env('PAY_API_KEY');
        $amount = 120000; // ریال
        $mobile = "09123456789"; // اختیاری
        $email = "example@gmail.com"; //اختیاری
        $description = "خرید کالا";
        $callback = env('PAY_CALLBACK_URL');

        $result = $this->token($api, $amount, $callback, $mobile, $email, $description);
        $result = json_decode($result);

        if (!empty($result->success)) {
            $_SESSION['token'] = $result->result->token;
            return $this->successResponse([
                'url' => $result->result->url
            ]);
        } else {
            return $this->errorResponse('تراکنش با خطا مواجه شد.', 422);
        }
    }

    public function token($api, $amount, $callback, $mobile, $email, $description)
    {
        return $this->curl_post('https://sandbox.shepa.com/api/v1/token', [
            'api' => $api,
            'amount' => $amount,
            'callback' => $callback,
            'mobile' => $mobile,
            'email' => $email,
            'description' => $description,
        ]);
    }

    public function curl_post($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}
