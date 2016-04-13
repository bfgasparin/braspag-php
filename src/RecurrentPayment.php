<?php

namespace Braspag;

/**
 * Recurrent payment configuration.
 *
 * @version 1.0
 */
class RecurrentPayment extends Model
{
    public $recurrentPaymentId;
    public $reasonCode;
    public $reasonMessage;
    public $nextRecurrency;
    public $startDate;
    public $endDate;
    public $interval;
    public $link;
    public $authorizeNow;
}
