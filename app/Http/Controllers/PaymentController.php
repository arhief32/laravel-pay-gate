<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mapping;
use App\Payment;

class PaymentController extends Controller
{
    public function requestPayment(Request $request)
    {
        if($request->input('BrivaNum') == null)
        {
            return response()->json([
                'isError' => '1',
                'errorCode' => '01',
                'errorDesc' => 'No Tagihan harus ada'    
            ]);
        }
        if($request->input('IdApp') == null)
        {
            return response()->json([
                'isError' => '1',
                'errorCode' => '13',
                'errorDesc' => 'ID User aplikasi harus ada'    
            ]);
        }
        if($request->input('PassApp') == null)
        {
            return response()->json([
                'isError' => '1',
                'errorCode' => '14',
                'errorDesc' => 'Password aplikasi harus ada'    
            ]);
        }
        else
        {
            $briva_number = substr($request->input('BrivaNum'), 0,5);
            $mapping = Mapping::where('corp_code', $briva_number)->first();
            
            if($mapping == null)
            {
                return response()->json([
                    'isError' => '1',
                    'errorCode' => '12',
                    'errorDesc' => 'Koneksi ke API gagal'    
                ]);
            }
            else
            {
                $client = new \GuzzleHttp\Client();
                
                try 
                {
                    $getUser = $client->request('GET', $mapping->url.'/get-user-detail/'.$request->input('BrivaNum'), 
                        array(
                            'timeout' => 15, // Response timeout
                            'connect_timeout' => 15, // Connection timeout
                        ))->getBody()->getContents();
                    $user = json_decode($getUser);
                } 
                catch (\Exception $e) 
                {
                    return response()->json([
                        'isError' => '1',
                        'errorCode' => '10',
                        'errorDesc' => 'Koneksi Timeout'    
                    ]);
                }

                if(!http_response_code(500))
                {
                    return response()->json([
                        'isError' => '1',
                        'errorCode' => '21',
                        'errorDesc' => 'Error query di database'    
                    ]);
                }

                if(!isset($user))
                {
                    return response()->json([
                        'isError' => '1',
                        'errorCode' => '99',
                        'errorDesc' => 'Undefined'    
                    ]);
                }

                if(!isset($user->id))
                {
                    return response()->json([
                        'isError' => '1',
                        'errorCode' => '14',
                        'errorDesc' => 'Data tagihan tidak ditemukan'    
                    ]);
                }
                else
                {
                    $statusPayment = [
                        'isError' => '0',
                        'errorCode' => '00',
                        'errorDesc' => 'Sukses'
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
        }
    }
}
