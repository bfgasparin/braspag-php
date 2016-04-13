<?php

namespace Braspag;

class DebitCardPayment extends Payment
{
    public $serviceTaxAmount;
    public $debitCard;
    public $authenticationUrl;
    public $authorizationCode;
    public $proofOfSale;
    public $acquirerTransactionId;
    public $softDescriptor;
    public $eci;
    
    public function __construct(array $params = []){
        $this->type = "DebitCard";
        $this->authenticate = Braspag::$defaultAuthenticate;
        $this->capture = Braspag::$defaultCapture;
        $this->interest = Braspag::$defaultInterest;

        parent::__construct($params);
    }
}
