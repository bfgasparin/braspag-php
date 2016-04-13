<?php

namespace Braspag;

class FraudAnalysisReplyData extends Model
{
    public $addressInfoCode;
    public $factorCode;
    public $score;
    public $binCountry;
    public $cardIssuer;
    public $cardScheme;
    public $hostSeverity;
    public $internetInfoCode;
    public $ipRoutingMethod;
    public $scoreModelUsed;
    public $casePriority;
}
