<?php

namespace Braspag;

class Card extends Model
{
    public $cardNumber;
    public $holder;
    public $expirationDate;
    public $securityCode;
    public $saveCard;
    public $cardToken;
    public $cardAlias;
    public $brand;
}
