<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\OrderController;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class PaymentController extends ApiController
{
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'order_items' => 'required',
            'order_items.*.product_id' => 'required',
            'order_items.*.quantity' => 'required|integer',
            'request_from' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $totalAmount = 0;
        $deliveryAmount = 0;
        foreach ($request->order_items as $orderItem) {
            $product = Product::findOrFail($orderItem['product_id']);
            if ($product->quantity < $orderItem['quantity']) {
                return $this->errorResponse("این تعداد از محصول $product->name در انبار موجود نمی باشد.", 422);
            }

            $totalAmount += $product->price * $orderItem['quantity'];
            $deliveryAmount += $product->delivery_amount;
        }

        $payingAmount = $totalAmount + $deliveryAmount;

        $amounts = [
            "totalAmount" => $totalAmount,
            "deliveryAmount" => $deliveryAmount,
            "payingAmount" => $payingAmount
        ];

        $api = env('PAY_API_KEY');
        $amount = $payingAmount . '0'; // ریال
        $mobile = "09123456789"; // اختیاری
        $email = "example@gmail.com"; //اختیاری
        $description = "خرید کالا";
        $callback = env('PAY_CALLBACK_URL');

        $result = $this->token($api, $amount, $callback, $mobile, $email, $description);
        $result = json_decode($result);

        if (!empty($result->success)) {
            OrderController::create($request, $amounts, $result->result->token);
            return $this->successResponse([
                'url' => $result->result->url
            ]);
        } else {
            return $this->errorResponse('تراکنش با خطا مواجه شد.', 422);
        }
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->messages(), 422);
        }

        $transaction = Transaction::where('token', $request->token)->firstOrFail();
        if (!empty($request->status) && $request->status == "success") {
            $api = 'sandbox';
            $token = $request->token;
            $amount = $transaction->amount . '0';
            $result = json_decode($this->verifyRequest($api, $token, $amount));
            if (!empty($result->success)) {
                OrderController::update($request->token, $result->result->transaction_id);
                return $this->successResponse([
                    'message' => 'تراکنش با موفقیت انجام شد',
                    'result' => $result
                ], 200);
            } else {
                return $this->errorResponse([
                    'message' => 'تراکنش با خطا مواجه شد.',
                    'errors' => $result->error
                ], 422);
            }
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

    public function verifyRequest($api, $token, $amount)
    {
        return $this->curl_post('https://sandbox.shepa.com/api/v1/verify', [
            'api' => $api,
            'token' => $token,
            'amount' => $amount,
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
