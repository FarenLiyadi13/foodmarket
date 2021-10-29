<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Midtrans\Config;
use Illuminate\Http\Request;
use Midtrans\Notification;

class MidtransController extends Controller
{
    public function callback(Request $request){
        // set konfigurasi midtrans
            Config::$serverKey = config('services.midtrans.serverkey');
            Config::$isProduction = config('services.midtrans.isProduction');
            Config::$isSanitized = config('services.midtrans.isSanitized');
            Config::$is3ds = config('services.midtrans.is3ds');


        // buat instance midtrans notification
            $notification = new Notification();

        // asssign ke variavble untuk memudahkan kodingan
            $status = $notification->transaction_status;
            $type = $notification->payment_type;
            $fraud = $notification->fraud_status;
            $order_id = $notification->order_id;

        // cari transasksi berdasarkan id
            $transaction = Transaction::findOrFail($order_id);
        // handle notifikasi
            if($status== 'capture'){
                if($type=='card'){
                    if($fraud == 'challenge'){
                        $transaction->status = 'PENDING';
                    }
                    else{
                        $transaction->status = 'SUCCESS';
                    }
                }
            }
            else if ($status=='settlement'){
            $transaction->status = 'SUCCESS';
            
            }
            else if ($status=='pending'){
            $transaction->status = 'PENDING';
            
            }
            else if ($status=='deny'){
            
            $transaction->status = 'CANCELLED';
            }
            else if ($status=='expired'){
            $transaction->status = 'CANCELLED';
            
            }
            else if ($status=='cancel'){
            $transaction->status = 'CANCELLED';
            
            }
            // simpan transaksi
            $transaction->save();

    }

    public function success(){
        return view('midtrans.success');
    }
    public function unfinish(){
        return view('midtrans.unfinish');
    }
    public function error(){
        return view('midtrans.error');
    }
}
