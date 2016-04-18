<?php

namespace Braspag\Http;

use Braspag\Card;
use Braspag\Link;
use Braspag\Sale;
use Braspag\Address;
use Braspag\Braspag;
use Braspag\Payment;
use Braspag\Customer;
use Httpful\Request;
use Braspag\BoletoPayment;
use Braspag\DebitCardPayment;
use Braspag\CreditCardPayment;
use Braspag\FraudAnalysisReplyData;

/**
 * Holds Braspag Bindings.
 *
 * Base on ApiServices from braspag/braspagapiphpsdk
 * 
 * @version 1.0
 * @author Bruno Gasparin <bfgasparin@gmail.com>
 */
class Sales
{
    public function __construct($merchantId = null, $merchantKey = null)
    {
        $this->headers = [
            'MerchantId' => is_null($merchantId) ? Braspag::getMerchantId() : $merchantId,
            'MerchantKey' => is_null($merchantKey) ? Braspag::getMerchantKey() : $merchantKey
        ];
    }
    
    /**
     * Creates a sale
    
     * @param Sale $sale 
     * @return mixed
     */
    public function createSale(Sale $sale){

        $uri = Braspag::$apiBase . 'sales'; 

        $request = json_encode($sale, JSON_UNESCAPED_UNICODE);
        
        $response = Request::post($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->body($request)            
            ->send();
        
        if($response->code == HttpStatus::Created){            
            $responsePayment = $response->body->Payment;

            $sale->payment->paymentId = $responsePayment->PaymentId;
            $sale->payment->status = $responsePayment->Status;
            $sale->payment->reasonCode = $responsePayment->ReasonCode;
            $sale->payment->reasonMessage = $responsePayment->ReasonMessage;
            $sale->payment->currency = $responsePayment->Currency;
            $sale->payment->country = $responsePayment->Country;
            $sale->payment->receivedDate = Utils::getResponseValue($responsePayment, 'ReceivedDate');
            $sale->capturedDate = Utils::getResponseValue($responsePayment, 'CapturedDate');
            $sale->voidedDate = Utils::getResponseValue($responsePayment, 'VoidedDate');
            $sale->capturedAmount = Utils::getResponseValue($responsePayment, 'CapturedAmount');
            $sale->capturedAmount = Utils::getResponseValue($responsePayment, 'VoidedAmount');
            $sale->payment->providerReturnCode = Utils::getResponseValue($responsePayment, 'ProviderReturnCode');
            $sale->payment->providerReturnMessage = Utils::getResponseValue($responsePayment, 'ProviderReturnMessage');
            
            if($responsePayment->Type == 'CreditCard' || $responsePayment->Type == 'DebitCard'){
                $sale->payment->authenticationUrl = Utils::getResponseValue($responsePayment, 'AuthenticationUrl');
                $sale->payment->authorizationCode = Utils::getResponseValue($responsePayment, 'AuthorizationCode');
                $sale->payment->acquirerTransactionId = Utils::getResponseValue($responsePayment, 'AcquirerTransactionId');
                $sale->payment->proofOfSale = Utils::getResponseValue($responsePayment, 'ProofOfSale');
				$sale->payment->creditCard = Utils::getResponseValue($responsePayment, 'CreditCard');

                if(Utils::getResponseValue($responsePayment, 'FraudAnalysis') != null){
                    $antiFraudResponse = Utils::getResponseValue($responsePayment, 'FraudAnalysis');

                    $replyData = new FraudAnalysisReplyData();
                    $replyData->addressInfoCode = Utils::getResponseValue($antiFraudResponse->ReplyData, 'AddressInfoCode');
                    $replyData->factorCode = Utils::getResponseValue($antiFraudResponse->ReplyData, 'FactorCode');
                    $replyData->score = Utils::getResponseValue($antiFraudResponse->ReplyData, 'Score');
                    $replyData->binCountry = Utils::getResponseValue($antiFraudResponse->ReplyData, 'BinCountry');
                    $replyData->cardIssuer = Utils::getResponseValue($antiFraudResponse->ReplyData, 'CardIssuer');
                    $replyData->cardScheme = Utils::getResponseValue($antiFraudResponse->ReplyData, 'CardScheme');
                    $replyData->hostSeverity = Utils::getResponseValue($antiFraudResponse->ReplyData, 'HostSeverity');
                    $replyData->internetInfoCode = Utils::getResponseValue($antiFraudResponse->ReplyData, 'InternetInfoCode');
                    $replyData->ipRoutingMethod = Utils::getResponseValue($antiFraudResponse->ReplyData, 'IpRoutingMethod');
                    $replyData->scoreModelUsed = Utils::getResponseValue($antiFraudResponse->ReplyData, 'ScoreModelUsed');
                    $replyData->casePriority = Utils::getResponseValue($antiFraudResponse->ReplyData, 'CasePriority');

                    $sale->payment->fraudAnalysis->status = $antiFraudResponse->Status;

                    $sale->payment->fraudAnalysis->replyData = $replyData;
                }

            }elseif($response->body->Payment->Type == 'Boleto'){
                $sale->payment->url = Utils::getResponseValue($responsePayment, 'Url');
                $sale->payment->barCodeNumber = Utils::getResponseValue($responsePayment, 'BarCodeNumber');
                $sale->payment->digitableLine = Utils::getResponseValue($responsePayment, 'DigitableLine');
                $sale->payment->boletoNumber = Utils::getResponseValue($responsePayment, 'BoletoNumber');

            }elseif($response->body->Payment->Type == 'EletronicTransfer'){    
                $sale->payment->url = Utils::getResponseValue($responsePayment, 'Url');                

            }            

            $recurrentResponse = Utils::getResponseValue($responsePayment, 'RecurrentPayment');

            if($recurrentResponse != null){
                $sale->payment->recurrentPayment->recurrentPaymentId = Utils::getResponseValue($recurrentResponse, 'RecurrentPaymentId');
                $sale->payment->recurrentPayment->reasonCode = $recurrentResponse->ReasonCode;
                $sale->payment->recurrentPayment->reasonMessage = $recurrentResponse->ReasonMessage;
                $sale->payment->recurrentPayment->nextRecurrency = Utils::getResponseValue($recurrentResponse, 'NextRecurrency');
                $sale->payment->recurrentPayment->startDate = Utils::getResponseValue($recurrentResponse, 'StartDate');
                $sale->payment->recurrentPayment->endDate = Utils::getResponseValue($recurrentResponse, 'EndDate');
                $sale->payment->recurrentPayment->interval = Utils::getResponseValue($recurrentResponse, 'Interval');
                $sale->payment->recurrentPayment->link = $this->parseLink(Utils::getResponseValue($recurrentResponse, 'Link'));
            }

            $sale->payment->links = $this->parseLinks($response->body->Payment->Links);
            
            return $sale;
        }elseif($response->code == HttpStatus::BadRequest){          
            return Utils::getBadRequestErros($response->body);             
        }  
        
        return $response->code;
    }
    
    /**
     * Captures a pre-authorized payment
     * @param GUID $paymentId 
     * @param CaptureRequest $captureRequest 
     * @return mixed
     */
    public function capture($paymentId, CaptureRequest $captureRequest){        
        $uri = Braspag::$apiBase . "sales/{$paymentId}/capture"; 
        
        if($captureRequest != null){
            $uri = $uri . "?amount={$captureRequest->amount}&serviceTaxAmount={$captureRequest->serviceTaxAmount}";
        }
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->send();
        
        if($response->code == HttpStatus::Ok){    
            
            $captureResponse = new CaptureResponse();
            $captureResponse->status = $response->body->Status;
            $captureResponse->reasonCode = $response->body->ReasonCode;
            $captureResponse->reasonMessage = $response->body->ReasonMessage;
            
            $captureResponse->links = $this->parseLinks($response->body->Links);
            
            return $captureResponse;
            
        }elseif($response->code == HttpStatus::BadRequest){            
            return Utils::getBadRequestErros($response->body);            
        }   
        
        return $response->code;
    }
    
    /**
     * Void a payment
     * @param GUID $paymentId 
     * @param int $amount 
     * @return mixed
     */
    public function void($paymentId, $amount){
        $uri = Braspag::$apiBase . "sales/{$paymentId}/void"; 
        
        if($amount != null){
            $uri = $uri . "?amount={$amount}";
        }
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->send();
        
        if($response->code == HttpStatus::Ok){    
            
            $voidResponse = new VoidResponse();
            $voidResponse->status = $response->body->Status;
            $voidResponse->reasonCode = $response->body->ReasonCode;
            $voidResponse->reasonMessage = $response->body->ReasonMessage;
            
            $voidResponse->links = $this->parseLinks($response->body->Links);
            
            return $voidResponse;
            
        }elseif($response->code == HttpStatus::BadRequest){            
            return Utils::getBadRequestErros($response->body);            
        }   
        
        return $response->code;
    }    
    
    /**
     * Gets a sale
     * @param GUID $paymentId 
     * @return mixed
     */
    public function get($paymentId){
        $uri = Braspag::$apiQueryBase . "sales/{$paymentId}"; 
        $response = Request::get($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->send();
        if($response->code == HttpStatus::Ok){    
            return new Sale(json_decode($response->raw_body, true));
            
        }elseif($response->code == HttpStatus::BadRequest){            
            return Utils::getBadRequestErros($response->body);            
        }   
        
        return $response->code;
    }
    
    private function parseLink($source){
        if($source == null) return null;

        $link = new Link();
        $link->href = $source->Href;
        $link->method = $source->Method;
        $link->rel = $source->Rel;

        return $link;
    }

    private function parseLinks($source){        
        $linkCollection = array();
        
        foreach ($source as $l)
        {
            $link = $this->parseLink($l);            
            array_push($linkCollection, $link);
        }
        
        return $linkCollection;
    }
}
