<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;
use App\IdUserApp;
use Validator;

class InquiryController extends Controller
{
    public function requestInquiry(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'IdApp' => 'required|max:8',
            'PassApp' => 'required|max:40',
            'TransmisiDateTime' => 'max:14', 
            'BankID' => 'max:3', 
            'TerminalID' => 'max:1',
            'BrivaNum'  => 'required|max:18',
        ]);
        
        if ($validator->fails()) {
            return response(
                $validator->errors(),
                400
            );
        }

        $response = [
            'BillDetail' => '',
            'Info1' => '',
            'Info2' => '',
            'Info3' => '',
            'Info4' => '',
            'Info5' => '',
            'StatusBill' => '',
            'Currency' => '',
        ];

        if($request->IdApp == IdUserApp::IdApp && $request->PassApp == IdUserApp::PassApp)
        {
            $briva_number = substr($request->input('BrivaNum'), 0,5);
    
            $mapping = Mapping::where('corp_code', $briva_number)->first();
            
            $client = new \GuzzleHttp\Client();
            $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->input('BrivaNum'))->getBody();
            $inquiries = json_decode($getInquiry);
    
            if($inquiries == false)
            {
                return $response;
            }
            else
            {
                $BillAmount = [];
                
                foreach($inquiries as $inquiry)
                {
                    array_push($BillAmount, $inquiry->amount);
                }
                
                $BillDetail = [
                    'BillAmount' => (string)array_sum($BillAmount),
                    'BillName' => $inquiries[0]->name,
                    'BrivaNum' => $request->BrivaNum,
                ];

                $response['BillDetail'] = $BillDetail;
                $response['Info1'] = 'Tagihan';
                $response['StatusBill'] = (string)$inquiries[0]->paidstatus;
                $response['Currency'] = 'IDR';

                return response()->json($response);
            }
        }
        else
        {
            return response()->json($response);
        }
    }

    // public function requestInquiry(Request $request)
    // {
    //     $validator = Validator::make(request()->all(), [
    //         'brivaNo'  => 'max:18'
    //     ]);
        
    //     if ($validator->fails()) {
    //         return response(
    //             $validator->errors(),
    //             400
    //         );
    //     }

    //     $validator = Validator::make(request()->all(), [
    //         'brivaNo'  => 'required|numeric'
    //     ]);
        
    //     if ($validator->fails()) {
    //         return response(
    //             $validator->errors(),
    //             400
    //         );
    //     }

    //     $briva_number = substr($request->input('brivaNo'), 0,5);

    //     $mapping = Mapping::where('corp_code', $briva_number)->first();
        
    //     $client = new \GuzzleHttp\Client();
    //     $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->input('brivaNo'))->getBody();
    //     $inquiries = json_decode($getInquiry);

    //     if($inquiries == false)
    //     {
    //         return [
    //             'responseCode' => '99',
    //             'responseDesc' => 'BRIVA number not found',
    //             'responseData' => (object)$inquiries
    //         ];
    //     }
    //     else
    //     {
    //         // define custDetailInvoice object
    //         $detail_invoice = [];
    
    //         foreach ($inquiries as $inquiry)
    //         {   
                
    //             array_push($detail_invoice, 
    //             [
    //                 'feeType' => $inquiry->feetype,
    //                 'amount' => $inquiry->amount
    //             ]);
    //         }
            
    //         // define responseData object
    //         $inquiry_amount_total = array();
    
    //         foreach ($inquiries as $inquiry)
    //         {   
    //             array_push($inquiry_amount_total, $inquiry->amount);
    //         }
    
    //         $response_data = [
    //             'custNo' => $inquiries[0]->registerNO,
    //             'custName' => $inquiries[0]->name,
    //             'custSumAmount' => array_sum($inquiry_amount_total),
    //             'custDetailInvoice' => $detail_invoice
    //         ];
            
    //         return response()->json([
    //             'responseCode' => '00',
    //             'responseDesc' => 'Inquiry Success',
    //             'responseData' => $response_data
    //         ]);
    //     }
    // }
}
