<?php

namespace Braspag\Http;

use Braspag\Card;
use Braspag\Link;
use Braspag\Sale;
use Braspag\Utils;
use Braspag\Address;
use Braspag\Braspag;
use Braspag\Payment;
use Braspag\Customer;
use Httpful\Request;
use Braspag\HttpStatus;
use Braspag\VoidResponse;
use Braspag\BoletoPayment;
use Braspag\CaptureRequest;
use Braspag\CaptureResponse;
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
    function __construct(){
        $this->headers = array(
                'MerchantId' => Braspag::getMerchantId(),
                'MerchantKey' => Braspag::getMerchantKey()
            );
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
            $sale = new Sale();
            $sale->merchantOrderId = $response->body->MerchantOrderId;
            $sale->customer = $this->parseCustomer($response->body->Customer);
            $sale->payment = $this->parsePayment($response->body->Payment);
            return $sale;
            
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
    
    private function parseCustomer($apiCustomer){
        $customer = new Customer();
        $customer->name = $apiCustomer->Name;
        $customer->email = Utils::getResponseValue($apiCustomer, 'Email');
        $customer->identity = Utils::getResponseValue($apiCustomer, 'Identity');
        $customer->identityType = Utils::getResponseValue($apiCustomer, 'IdentityType');
        $customer->birthDate = Utils::getResponseValue($apiCustomer, 'Birthdate');
        
        $apiAddress = Utils::getResponseValue($apiCustomer, 'Address');
        if($apiAddress != null){
            $address = new Address();
            $address->country = $apiAddress->Country;
            $customer->city = Utils::getResponseValue($apiAddress, 'City');
            $customer->complement = Utils::getResponseValue($apiAddress, 'Complement');
            $customer->district = Utils::getResponseValue($apiAddress, 'District');
            $customer->number = Utils::getResponseValue($apiAddress, 'Number');
            $customer->state = Utils::getResponseValue($apiAddress, 'State');
            $customer->street = Utils::getResponseValue($apiAddress, 'Street');
            $customer->zipCode = Utils::getResponseValue($apiAddress, 'ZipCode');
            $customer->address = $address;
        }
        
        return $customer;
    }
    
    private function parsePayment($apiPayment){
        $payment = new Payment();

        if($apiPayment->Type == 'CreditCard'){
            $payment = new CreditCardPayment();
            $this->parseCreditAndDebitPayment($payment, $apiPayment, $apiPayment->CreditCard);
            
            $payment->capture = $apiPayment->Capture;
            $payment->authenticate = $apiPayment->Authenticate;
            $payment->installments = $apiPayment->Installments;
            
        }elseif($apiPayment->Type == 'DebitCard'){
            $payment = new DebitCardPayment();
            $this->parseCreditAndDebitPayment($payment, $apiPayment, $apiPayment->DebitCard);

        }elseif($apiPayment->Type == 'Boleto') {
            $payment = new BoletoPayment();    

            $payment->url = Utils::getResponseValue($apiPayment, 'Url');
            $payment->barCodeNumber = Utils::getResponseValue($apiPayment, 'BarCodeNumber');
            $payment->digitableLine = Utils::getResponseValue($apiPayment, 'DigitableLine');
            $payment->boletoNumber = Utils::getResponseValue($apiPayment, 'BoletoNumber');
            
            $payment->instructions = Utils::getResponseValue($apiPayment, 'Instructions');
            $payment->expirationDate = Utils::getResponseValue($apiPayment, 'ExpirationDate');
            $payment->demonstrative = Utils::getResponseValue($apiPayment, 'Demonstrative');
            $payment->assignor = Utils::getResponseValue($apiPayment, 'Assignor');
            $payment->address = Utils::getResponseValue($apiPayment, 'Address');
            $payment->identification = Utils::getResponseValue($apiPayment, 'Identification');

        }elseif($apiPayment->Type == 'EletronicTransfer'){
            $payment->url = Utils::getResponseValue($apiPayment, 'Url');
        }
        
        $payment->paymentId = $apiPayment->PaymentId;
        $payment->amount = $apiPayment->Amount;
        $payment->capturedAmount = Utils::getResponseValue($apiPayment, 'CapturedAmount');
        $payment->capturedAmount = Utils::getResponseValue($apiPayment, 'VoidedAmount');
        $payment->receivedDate = $apiPayment->ReceivedDate;
        $payment->capturedDate = Utils::getResponseValue($apiPayment, 'CapturedDate');
        $payment->voidedDate = Utils::getResponseValue($apiPayment, 'VoidedDate');
        $payment->country = $apiPayment->Country;
        $payment->currency = $apiPayment->Currency;
        $payment->provider = $apiPayment->Provider;
        $payment->status = $apiPayment->Status;
        $payment->reasonCode = $apiPayment->ReasonCode;
        $payment->reasonMessage = $apiPayment->ReasonMessage;
        $payment->providerReturnCode = Utils::getResponseValue($apiPayment, 'ProviderReturnCode');
        $payment->providerReturnMessage = Utils::getResponseValue($apiPayment, 'ProviderReturnMessage');
        $payment->returnUrl = Utils::getResponseValue($apiPayment, 'ReturnUrl');        
        $payment->links = $this->parseLinks($apiPayment->Links);
        
        return $payment;
    }
    
    private function parseCreditAndDebitPayment($payment, $apiPayment, $card){
        $payment->authenticationUrl = Utils::getResponseValue($apiPayment, 'AuthenticationUrl');
        $payment->authorizationCode = Utils::getResponseValue($apiPayment, 'AuthorizationCode');
        $payment->acquirerTransactionId = Utils::getResponseValue($apiPayment, 'AcquirerTransactionId');
        $payment->proofOfSale = Utils::getResponseValue($apiPayment, 'ProofOfSale');
        $payment->eci = Utils::getResponseValue($apiPayment, 'Eci');

        $parsedCard = new Card();
        $parsedCard->brand = $card->Brand;
        $parsedCard->cardNumber = $card->CardNumber;
        $parsedCard->expirationDate = $card->ExpirationDate;
        $parsedCard->holder = $card->Holder;
        $payment->creditCard = $parsedCard;
    }  
}
