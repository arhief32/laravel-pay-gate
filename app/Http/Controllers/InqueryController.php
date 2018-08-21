<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;
use App\Inquery;

class InqueryController extends Controller
{
    public function requestInquery(Request $request)
    {
        $briva_number = substr($request->input('BrivaNum'), 0,5);

        $mapping = Mapping::where('corp_code', $briva_number)->first();

        $client = new \GuzzleHttp\Client();
        $getUser = $client->request('GET', $mapping->url.'/get-user-detail/'.$request->input('BrivaNum'))->getBody();
        $user = json_decode($getUser);
        
        $bill_detail = [
            'BillAmount' => $user->bill_amount,
            'BillName' => $user->bill_name,
            'BrivaNum' => $user->briva_number
        ];
        
        return response()->json([
            'BillDetail' => $bill_detail,
            'Info1' => $user->info_1,
            'Info2' => $user->info_2,
            'Info3' => $user->info_3,
            'Info4' => $user->info_4,
            'Info5' => $user->info_5,
            'StatusBill' => $user->bill_status,
            'Currency' => $user->currency
        ]);
    }
}
