<?php

namespace Braspag;

class Braspag
{
    public static $merchantId;
    public static $merchantKey;
	public static $apiBase = 'https://apihomolog.braspag.com.br/v2/';
    public static $apiQueryBase = 'https://apiqueryhomolog.braspag.com.br/v2/';
    public static $defaultCurrency = 'BRL';
    public static $defaultCountry = 'BRA';
    public static $defaultInterest = 'ByMerchant';
    public static $defaultCapture = false;
    public static $defaultAuthenticate = false;
    public static $defaultSequence = "AuthorizeFirst";
    public static $defaultSequenceCriteria = "OnSuccess";

    /**
     * @return string The MerchantId used for requests.
     */
    public static function getMerchantId()
    {
        return self::$merchantId;
    }    

   	/**
     * Sets the MerchantId to be used for requests.
     *
     * @param string $merchantId
     */
    public static function setMerchantId($merchantId)
    {
        self::$merchantId = $merchantId;
    }

    /**
     * @return string The MerchantKey used for requests.
     */
    public static function getMerchantKey()
    {
        return self::$merchantKey;
    }    

   	/**
     * Sets the MerchantKey to be used for requests.
     *
     * @param string $merchantKey
     */
    public static function setMerchantKey($merchantKey)
    {
        self::$merchantKey = $merchantKey;
    }
    
}