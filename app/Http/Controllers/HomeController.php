<?php

namespace App\Http\Controllers;

use FedaPay;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        FedaPay\FedaPay::setApiKey(config('fedapay.secret_key'));
        FedaPay\FedaPay::setEnvironment(config('fedapay.environment'));
    }

    public function process(Request $request)
    {
        try {
            $transaction = FedaPay\Transaction::create(
                $this->fedapayTransactionData()
            );

            $token = $transaction->generateToken();

            return redirect()->away($token->url);
        } catch(\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function fedapayTransactionData()
    {
        $customer_data = [
            'firstname' => 'Junior',
            'lastname' => 'Gantin',
            'email' => 'nioperas06@gmail.com',
            'phone_number' => '66526416',
            'country' => 'bj'
        ];

        return [
            'description' => 'Buy Camo Fang Backpack Jungle!',
            'amount' => 500,
            'currency' => ['iso' => 'XOF'],
            'callback_url' => url('callback'),
            'customer' => $customer_data
        ];
    }

    public function callback(Request $request)
    {
        $transaction_id = $request->input('id');
        $message = '';

        try {
            $transaction = FedaPay\Transaction::retrieve($transaction_id);
            switch($transaction->status) {
                case 'approved':
                    $message = 'Transaction approuvée.';
                break;
                case 'canceled':
                    $message = 'Transaction annulée.';
                break;
                case 'declined':
                    $message = 'Transaction déclinée.';
                break;
            }

        } catch(\Exception $e) {
            $message = $e->getMessage();
        }

        return view('callback', compact('message'));
    }
}
