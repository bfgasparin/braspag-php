<?php

namespace Braspag;

/**
 * Define BoletoPayment model.
 *
 * @version 1.0
 * @author interatia
 */

class BoletoPayment extends Payment
{
    public $address;
    public $assignor;
    public $barCodeNumber;
    public $boletoNumber;
    public $demonstrative;
    public $digitableLine;
    public $expirationDate;
    public $identification;
    public $instructions;
    public $url;
    
    public function __construct(array $params = []){
        $this->type = "Boleto";        

        parent::__construct($params);
    }
}

?>
