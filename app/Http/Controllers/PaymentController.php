<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mapping;
use App\Payment;
use App\IdUserApp;
use GuzzleHttp\RequestOptions;
use Validator;

class PaymentController extends Controller
{
    public function requestPayment(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'IdApp' => 'required|max:8',
            'PassApp' => 'required|max:40',
            'TransmisiDateTime' => 'max:14', 
            'BankID' => 'max:3', 
            'TerminalID' => 'max:1',
            'BrivaNum'  => 'required|max:18',
            'PaymentAmount'  => 'required',
            'TransaksiID'  => 'required',
        ]);
        
        $response = [
            'StatusPayment' => [
                'ErrorDesc' => '',
                'ErrorCode' => '',
                'isError' => '',
            ],
            'Info1' => '',
            'Info2' => '',
            'Info3' => '',
            'Info4' => '',
            'Info5' => '',
        ];

        if ($validator->fails()) {
            if($validator->errors('IdApp') && $validator->errors('PassApp'))
            {
                $response['StatusPayment']['ErrorDesc'] = 'ID User dan Password aplikasi harus ada';
                $response['StatusPayment']['ErrorCode'] = '13';
                $response['StatusPayment']['isError'] = '1';
                return response()->json($response);
            }
            if($validator->errors('IdApp'))
            {
                $response['StatusPayment']['ErrorDesc'] = 'ID User aplikasi harus ada';
                $response['StatusPayment']['ErrorCode'] = '13';
                $response['StatusPayment']['isError'] = '1';
                return response()->json($response);
            }
            if($validator->errors('PassApp'))
            {
                $response['StatusPayment']['ErrorDesc'] = 'Password aplikasi harus ada';
                $response['StatusPayment']['ErrorCode'] = '14';
                $response['StatusPayment']['isError'] = '1';
                return response()->json($response);
            }
            if($validator->errors('BrivaNum'))
            {
                $response['StatusPayment']['ErrorDesc'] = 'No Tagihan harus ada';
                $response['StatusPayment']['ErrorCode'] = '01';
                $response['StatusPayment']['isError'] = '1';
                return response()->json($response);
            }
        }

        $briva_number = substr($request->BrivaNum, 0,5);
        $school_id = substr($request->BrivaNum, 5,4);

        $mapping = Mapping::where('corp_code', $briva_number)->first();
        
        $school_db = DB::connection('school-gateway')
        ->table('schooldb')
        ->select('*')
        ->where('schoolID',$school_id)
        ->first();
        
        if($mapping == false || $school_db == false)
        {
            $response['StatusPayment']['ErrorDesc'] = 'Data tagihan tidak ditemukan';
            $response['StatusPayment']['ErrorCode'] = '02';
            $response['StatusPayment']['isError'] = '1';

            return response()->json($response);
        }

        // request inquiry-payment
        $client = new \GuzzleHttp\Client();
        
        // request inquiry for get amount in invoice before do payment
        $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->BrivaNum)->getBody();
        $inquiries = json_decode($getInquiry);


        // Briva number validation
        if($inquiries == false)
        {
            $response['StatusPayment']['ErrorDesc'] = 'Data tagihan tidak ditemukan';
            $response['StatusPayment']['ErrorCode'] = '02';
            $response['StatusPayment']['isError'] = '1';
            
            return response()->json($response);
        }

        $inquiry_amount_total = array();
        
        foreach ($inquiries as $inquiry)
        {   
            array_push($inquiry_amount_total, $inquiry->amount);
        }

        if($request->sumAmount != array_sum($inquiry_amount_total))
        {
            $response['StatusPayment']['ErrorDesc'] = 'Jumlah nominal pembayaran tidak sama dengan Total Tagihan';
            $response['StatusPayment']['ErrorCode'] = '04';
            $response['StatusPayment']['isError'] = '1';
            
            return response()->json($response);
        }
        else
        {
            $setPayment = $client->request('POST', $mapping->corp_url.'request-payment', 
            [
                'json' => [
                    'briva_number' => $request->BrivaNum,
                    'journal_sequence' => 'BRIVA-'.$request->TransaksiID,
                    'detail_payments' => $inquiries
                ]
            ])->getBody();
            $payments = json_decode($setPayment);

            if($payments->status == 'gagal')
            {
                $response['StatusPayment']['ErrorDesc'] = 'Error query di database';
                $response['StatusPayment']['ErrorCode'] = '21';
                $response['StatusPayment']['isError'] = '1';
                
                return response()->json($response);
            }
            else
            {
                $response['StatusPayment']['ErrorDesc'] = 'Sukses';
                $response['StatusPayment']['ErrorCode'] = '00';
                $response['StatusPayment']['isError'] = '0';
                
                return response()->json($response);
            }
            
        }
    }
    

    // public function requestPayment(Request $request)
    // {
    //     $validator = Validator::make(request()->all(), [
    //         'brivaNo'  => 'max:18',
    //         'sumAmount' => 'required|numeric',
    //         'journalSeq' => 'required|numeric'
    //     ]);
        
    //     if ($validator->fails()) {
    //         return response(
    //             $validator->errors(),
    //             400
    //         );
    //     }

    //     $validator = Validator::make(request()->all(), [
    //         'brivaNo'  => 'required|numeric',
    //         'sumAmount' => 'required|numeric',
    //         'journalSeq' => 'required|numeric'
    //     ]);
        
    //     if ($validator->fails()) {
    //         return response(
    //             $validator->errors(),
    //             400
    //         );
    //     }
        
    //     $briva_number = substr($request->brivaNo, 0,5);

    //     $mapping = Mapping::where('corp_code', $briva_number)->first();
        
    //     // request inquiry-payment
    //     $client = new \GuzzleHttp\Client();
        
    //     // request inquiry for get amount in invoice before do payment
    //     $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->brivaNo)->getBody();
    //     $inquiries = json_decode($getInquiry);

    //     // Briva number validation
    //     $response_data = [];
    //     if($inquiries == false)
    //     {
    //         return response()->json([
    //             'responseCode' => '01',
    //             'responseDesc' => 'The Briva Number is Wrong or No Bill',
    //             'responseData' => (object)$response_data
    //         ]);
    //     }

    //     $inquiry_amount_total = array();
        
    //     foreach ($inquiries as $inquiry)
    //     {   
    //         array_push($inquiry_amount_total, $inquiry->amount);
    //     }

    //     $response_data = [];

    //     if($request->sumAmount != array_sum($inquiry_amount_total))
    //     {
    //         return response()->json([
    //             'responseCode' => '01',
    //             'responseDesc' => 'Sum Amount Not Match',
    //             'responseData' => (object)$response_data
    //         ]);
    //     }
    //     else
    //     {
    //         $setPayment = $client->request('POST', $mapping->corp_url.'request-payment', 
    //         [
    //             'json' => [
    //                 'briva_number' => $request->brivaNo,
    //                 'journal_sequence' => 'BRIVA-'.$request->journalSeq,
    //                 'detail_payments' => $inquiries
    //             ]
    //         ])->getBody();
    //         $payments = json_decode($setPayment);

    //         if($payments->status == 'gagal')
    //         {
    //             return response()->json([
    //                 'responseCode' => '00',
    //                 'responseDesc' => 'Internal Server Error',
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'responseCode' => '00',
    //                 'responseDesc' => 'Inquiry Success',
    //                 'responseData' => (object)$response_data
    //             ]);
    //         }
            
    //     }
    // }
}
