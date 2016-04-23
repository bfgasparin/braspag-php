<?php

namespace Braspag;

class CreditCardPayment extends Payment
{
    public $serviceTaxAmount;
    public $installments;
    public $interest;
    public $capture;
    public $authenticate;
    public $recurrent;
    public $creditCard;
    public $authenticationUrl;
    public $authorizationCode;
    public $proofOfSale;
    public $acquirerTransactionId;
    public $softDescriptor;
    public $eci;
    public $fraudAnalysis;    
    public $sentOrderId;
    public $providerDescription;
    
    public function __construct(array $params = []){
        $this->type = "CreditCard";
        $this->authenticate = Braspag::$defaultAuthenticate;
        $this->capture = Braspag::$defaultCapture;
        $this->interest = Braspag::$defaultInterest;

        parent::__construct($params);
    }
}
