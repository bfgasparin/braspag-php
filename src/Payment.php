<?php

namespace Braspag;

class Payment
{
    public $paymentId;
    public $type;
    public $amount;
    public $capturedAmount;
    public $voidedAmount;
    public $receivedDate;
    public $capturedDate;
    public $voidedDate;
    public $currency;
    public $country;
    public $provider;
    public $credentials;
    public $extraDatas;
    public $returnUrl;
    public $reasonCode;
    public $reasonMessage;
	public $providerReturnCode;
	public $providerReturnMessage;
    public $status;
    public $links;
    public $recurrentPayment;
    
    public function __construct(){
        $this->country = BraspagApiConfig::defaultCountry;
        $this->currency = BraspagApiConfig::defaultCurrency;
    }
}
