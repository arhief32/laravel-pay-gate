<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;
use App\Payment;
use GuzzleHttp\RequestOptions;
use Validator;

class PaymentController extends Controller
{
    public function requestPayment(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'brivaNo'  => 'max:18',
            'sumAmount' => 'required|numeric',
            'journalSeq' => 'required|numeric'
        ]);
        
        if ($validator->fails()) {
            return response(
                $validator->errors(),
                400
            );
        }

        $validator = Validator::make(request()->all(), [
            'brivaNo'  => 'required|numeric',
            'sumAmount' => 'required|numeric',
            'journalSeq' => 'required|numeric'
        ]);
        
        if ($validator->fails()) {
            return response(
                $validator->errors(),
                400
            );
        }
        
        $briva_number = substr($request->brivaNo, 0,5);

        $mapping = Mapping::where('corp_code', $briva_number)->first();
        
        // request inquiry-payment
        $client = new \GuzzleHttp\Client();
        
        // request inquiry for get amount in invoice before do payment
        $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->brivaNo)->getBody();
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
            $setPayment = $client->request('POST', $mapping->corp_url.'request-payment', 
            [
                'json' => [
                    'briva_number' => $request->brivaNo,
                    'journal_sequence' => 'BRIVA-'.$request->journalSeq,
                    'detail_payments' => $inquiries
                ]
            ])->getBody();
            $payments = json_decode($setPayment);

            if($payments->status == 'gagal')
            {
                return response()->json([
                    'responseCode' => '00',
                    'responseDesc' => 'Internal Server Error',
                ]);
            }
            else
            {
                return response()->json([
                    'responseCode' => '00',
                    'responseDesc' => 'Inquiry Success',
                    'responseData' => (object)$response_data
                ]);
            }
            
        }
    }
}
