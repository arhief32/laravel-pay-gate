<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;

class InquiryController extends Controller
{
    public function requestInquiry(Request $request)
    {
        $briva_number = substr($request->BrivaNum, 0,5);

        $mapping = Mapping::where('corp_code', $briva_number)->first();
        
        $client = new \GuzzleHttp\Client();
        $getInquiry = $client->request('GET', $mapping->corp_url.'request-inquiry?BrivaNum='.$request->BrivaNum)->getBody();
        $inquiry = json_decode($getInquiry);

        return response()->json($inquiry);
    }
}
