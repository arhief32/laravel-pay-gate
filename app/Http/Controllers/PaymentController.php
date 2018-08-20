<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;
use App\Payment;

class PaymentController extends Controller
{
    public function requestPayment(Request $request)
    {
        $briva_number = substr($request->input('BrivaNum'), 0,5);

        $mapping = Mapping::where('corp_code', $briva_number)->first();

        $client = new \GuzzleHttp\Client();
        $getUser = $client->request('GET', $mapping->url.'/get-user-detail/'.$request->input('BrivaNum'))->getBody();
        $user = json_decode($getUser);
        
        $statusPayment = [
            'isError' => '0',
            'errorCode' => $user->status->error_code,
            'errorDesc' => $user->status->description
        ];
        
        return response()->json([
            'StatusPayment' => $statusPayment,
            'Info1' => $user->info_1,
            'Info2' => $user->info_2,
            'Info3' => $user->info_3,
            'Info4' => $user->info_4,
            'Info5' => $user->info_5
        ]);
    }
}
