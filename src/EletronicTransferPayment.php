<?php

namespace Braspag;

/**
 * Define EletronictTransferPayment model.
 *
 * @version 1.0
 */
class EletronicTransferPayment extends Payment
{
    public $url;

    public function __construct(){        
        $this->type = "EletronicTransfer";
    }
}
