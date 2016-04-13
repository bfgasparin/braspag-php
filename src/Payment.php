<?php

namespace Braspag;

class Payment extends Model
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
    
    public function __construct(array $params = []){
        $this->country = Braspag::$defaultCountry;
        $this->currency = Braspag::$defaultCurrency;

        parent::__construct($params);
    }

    protected function convertToModel($key, $params)
    { 
        $property = [];
        if ($key === 'links') {

            $types = $this->getTypes();
            foreach ($params as $link) {
                $property[] = new $types['link']($link);
            }

            return $property;
        }

        return parent::convertToModel($key, $params);
    }
}
