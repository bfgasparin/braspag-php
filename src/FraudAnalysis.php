<?php

namespace Braspag;

class FraudAnalysis extends Model {

	public $sequence;
	public $sequenceCriteria;
	public $fingerPrintId;
	public $captureOnLowRisk;
	public $voidOnHighRisk;
    public $status;

	public $browser;

	public $cart;

    public $replyData;

    public function __construct(array $params = []){
        $this->sequence = Braspag::$defaultSequence;
        $this->sequenceCriteria = Braspag::$defaultSequenceCriteria;

        parent::__construct($params);
    }
}