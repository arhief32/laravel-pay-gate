<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;
use App\Payment;
use GuzzleHttp\RequestOptions;


class PaymentController extends Controller
{
    public function requestPayment(Request $request)
    {
        $briva_number = substr($request->BrivaNum, 0,5);

        $mapping = Mapping::where('corp_code', $briva_number)->first();
        
        // request inquiry-payment
        $client = new \GuzzleHttp\Client();
        
        // request inquiry for get amount in invoice before do payment
        $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->BrivaNum)->getBody();
        $inquiries = json_decode($getInquiry);

        $inquiry_amount_total = array();
        
        foreach ($inquiries as $inquiry)
        {   
            array_push($inquiry_amount_total, $inquiry->amount);
        }

        $response_data = [];

        if($request->sumAmount != array_sum($inquiry_amount_total))
        {
            return response()->json([
                'responseCode' => '01',
                'responseDesc' => 'Sum Amount Not Match',
                'responseData' => (object)$response_data
            ]);
        }
        else
        {
            // $setPayment = $client->request('POST','localhost:8181/api/request-payment', 
            // [
            //     'json' => [
            //         'briva_number' => $request->BrivaNum,
            //         'journal_sequence' => 'BRIVA-'.$request->journalSequence,
            //         'detail_payments' => $inquiries
            //     ]
            // ])->getBody();
            // $payments = json_decode($setPayment);

            // return response()->json($payments);

            $response_data = ['journalSeq' => 'BRIVA-'.$request->journalSequence];
            
            return response()->json([
                'responseCode' => '00',
                'responseDesc' => 'Inquiry Success',
                'responseData' => (object)$response_data
            ]);
        }
    }
}
