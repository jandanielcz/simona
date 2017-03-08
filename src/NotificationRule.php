<?php


namespace JanDanielCz\Simona;


class NotificationRule
{

    public $intervalStart = null;
    public $intervalEnd = null;
    public $onlyIfUnsolved = false;
    public $targets = array();

    public $originalString = null;

    public function isConditionMet($daysToDelay, $isSolved)
    {
        if ($this->onlyIfUnsolved === true && $isSolved !== false) {
            return false;
        }

        var_dump($daysToDelay);
        var_dump($this->intervalStart);
        var_dump($this->intervalEnd);
        if (($daysToDelay <= $this->intervalStart) && ($daysToDelay >= $this->intervalEnd)) {
            var_dump('TR');
            return true;
        } else {
            return false;
        }

    }

} 