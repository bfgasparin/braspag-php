<?php

namespace Braspag\Http;

use Httpful\Request;

/**
 * Provide services to manage recurrencies
 *
 * @version 1.0
 */
class RecurrentSales
{
    function __construct(){
        $this->headers = array(
                'MerchantId' => Braspag::getMerchantId(),
                'MerchantKey' => Braspag::getMerchantKey()
            );
    }

    /**
     * Updates the customer of one recurrent payment
     * @param mixed $recurrentId 
     * @param BraspagCustomer $customer 
     */
    public function updateCustomer($recurrentId, $customer){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/Customer"; 

        $request = json_encode($customer, JSON_UNESCAPED_UNICODE);
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->body($request)            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }
    
    /**
     * Updates the EndDate of one recurrent payment
     * @param mixed $recurrentId 
     * @param $endDate 
     */    
    public function updateEndDate($recurrentId, $endDate){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/EndDate"; 
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->body('"'.$endDate.'"')            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }

    /**
     * Updates the EndDate of one recurrent payment
     * @param mixed $recurrentId 
     * @param $day 
     */
    public function updateDay($recurrentId, $day){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/RecurrencyDay"; 

        $request = json_encode($day, JSON_UNESCAPED_UNICODE);
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->body($request)            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }

    /**
     * Updates the EndDate of one recurrent payment
     * @param mixed $recurrentId 
     * @param $interval 
     */
    public function updateInterval($recurrentId, $interval){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/Interval"; 

        $request = json_encode($interval, JSON_UNESCAPED_UNICODE);
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->body($request)            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }

    /**
     * Updates the number of installments of one recurrent payment
     * @param mixed $recurrentId 
     * @param int $installments 
     */
    public function updateInstallments($recurrentId, $installments){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/Installments"; 
        
        $response = Request::put($uri)
            ->addHeaders($this->headers)
            ->addHeader("content-type", "text/json")
            ->body($installments)            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }

    /**
     * Updates the next payment date of one recurrent payment
     * @param mixed $recurrentId 
     * @param string $date 
     */
    public function updateNextPaymentDate($recurrentId, $date){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/NextPaymentDate"; 
        
        $response = Request::put($uri)
            ->sendsJson()
            ->addHeaders($this->headers)
            ->body('"'.$date.'"')            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }

    /**
     * Updates the amount of one recurrent payment
     * @param mixed $recurrentId 
     * @param int $amount 
     */
    public function updateAmount($recurrentId, $amount){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/Amount"; 
        
        $response = Request::put($uri)
            ->addHeaders($this->headers)
            ->addHeader("content-type", "text/json")
            ->body($amount)            
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    }  
    
    /**
     * Deactivate one recurrent payment
     * @param mixed $recurrentId 
     */
    public function deactivate($recurrentId, $amount){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/Deactivate"; 
        
        $response = Request::put($uri)
            ->addHeaders($this->headers)
            ->addHeader("content-type", "text/json")         
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    } 
    
    /**
     * Deactivate one recurrent payment
     * @param mixed $recurrentId 
     */
    public function reactivate($recurrentId, $amount){
        $uri = Braspag::$apiBase . "RecurrentPayment/$recurrentId/Reactivate"; 
        
        $response = Request::put($uri)
            ->addHeaders($this->headers)
            ->addHeader("content-type", "text/json")         
            ->send();

        if($response->code == HttpStatus::BadRequest){
            return Utils::getBadRequestErros($response->body);
        }

        return $response->code;
    } 
}
