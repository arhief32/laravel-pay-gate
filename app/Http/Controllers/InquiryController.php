<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;

class InquiryController extends Controller
{
    public function requestInquiry(Request $request)
    {
        $briva_number = substr($request->brivaNo, 0,5);

        $mapping = Mapping::where('corp_code', $briva_number)->first();
        
        $client = new \GuzzleHttp\Client();
        $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->brivaNo)->getBody();
        $inquiries = json_decode($getInquiry);

        if($inquiries == false)
        {
            return [
                'responseCode' => '99',
                'responseDesc' => 'BRIVA number not found',
                'responseData' => (object)$inquiries
            ];
        }
        else
        {
            // define custDetailInvoice object
            $detail_invoice = [];
    
            foreach ($inquiries as $inquiry)
            {   
                
                array_push($detail_invoice, 
                [
                    'feeType' => $inquiry->feetype,
                    'amount' => $inquiry->amount
                ]);
            }
            
            // define responseData object
            $inquiry_amount_total = array();
    
            foreach ($inquiries as $inquiry)
            {   
                array_push($inquiry_amount_total, $inquiry->amount);
            }
    
            $response_data = [
                'custNo' => $inquiries[0]->registerNO,
                'custName' => $inquiries[0]->name,
                'custSumAmount' => array_sum($inquiry_amount_total),
                'custDetailInvoice' => $detail_invoice
            ];
            
            return response()->json([
                'responseCode' => '00',
                'responseDesc' => 'Inquiry Success',
                'responseData' => $response_data
            ]);
        }
    }
}
