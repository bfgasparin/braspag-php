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
    
    public function __construct(){
        $this->type = "CreditCard";
        $this->authenticate = BraspagApiConfig::defaultAuthenticate;
        $this->capture = BraspagApiConfig::defaultCapture;
        $this->interest = BraspagApiConfig::defaultInterest;
    }
}
