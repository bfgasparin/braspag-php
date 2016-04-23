<?php

namespace Braspag;

class Sale extends Model
{
    public $merchantOrderId;
    public $customer;
	public $payment; 
	public $merchant; 

    protected function convertToModel($key, $params)
	{
		$paymentTypes = [
	        'Boleto' => 'Braspag\\BoletoPayment',
	        'CreditCard' => 'Braspag\\CreditCardPayment',
	        'DebitCard' => 'Braspag\\DebitCardPayment',
	        'EletronicTransfer' => 'Braspag\\EletronicTransferPayment',
   		];				

		if($key === 'payment'){
        	return new $paymentTypes[$params['Type']]($params);
		}
		
		return parent::convertToModel($key, $params);
	}
}

?>
