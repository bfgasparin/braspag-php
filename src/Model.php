<?php

namespace Braspag;

abstract class Model implements Arrayable
{
	public function __construct(array $params = [])
	{
		foreach ($params as $key => $value) {
			$property = lcfirst($key);
			if (is_array($value)){
				$propertyValue = $this->convertToModel($property, $value);
			}else {
				$propertyValue = $value;
			}
			$this->setProperty($property, $propertyValue);
		}
	}

	public function setProperty($property, $value)
	{
		if(!property_exists($this, $property)) {
			throw new \InvalidArgumentException(sprintf("'%s' is not a property for '%s' model", $property, static::class));
		}
		$this->{$property} = $value;

	}

	protected function convertToModel($key, $params)
	{ 
		$types = $this->getTypes();
        return new $types[$key]($params);
	}

	protected function getTypes()
	{
		return [
	        'address' => 'Braspag\\Address',
	        'creditCard' => 'Braspag\\Card',
	        'debitCard' => 'Braspag\\Card',
	        'cart' => 'Braspag\\Cart',
	        'cartItem' => 'Braspag\\CartItem',
	        'customer' => 'Braspag\\Customer',
	        'link' => 'Braspag\\Link',
	        'passenger' => 'Braspag\\Passenger',
	        'recurrentPayment' => 'Braspag\\RecurrentPayment',
	        'sale' => 'Braspag\\Sale',
	        'fraudAnalysis' => 'Braspag\\FraudAnalysis',
	        'replyData' => 'Braspag\\FraudAnalysisReplyData',
	        'browser' => 'Braspag\\Browser',
	        'passenger' => 'Braspag\\Passenger'
   		];	
	}

	public function toArray()
	{	
		$attributes = get_object_vars($this);

		return $this->convertAttributesToArray($attributes);
	}

	protected function convertAttributesToArray($array) : array
	{
		foreach ($array as $key => $value) {
			if (is_object($value)) {
				$array[$key] = $value->toArray();
			}else if (is_array($value)) {
				$array[$key] = $this->convertAttributesToArray($value);
			}
		}	

		return $array;
	}
}
