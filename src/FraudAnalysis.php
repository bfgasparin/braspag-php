<?php

namespace Braspag;

class FraudAnalysis {

	public $sequence;
	public $sequenceCriteria;
	public $fingerPrintId;
	public $captureOnLowRisk;
	public $voidOnHighRisk;
    public $status;

	public $browser;

	public $cart;

    public $replyData;

    public function __construct(){
        $this->sequence = Braspag::$defaultSequence;
        $this->sequenceCriteria = Braspag::$defaultSequenceCriteria;
    }
}