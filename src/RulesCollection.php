<?php

namespace JanDanielCz\Simona;


class RulesCollection
{

    protected $rules = [];
    protected $recipientReducer = null;

    public function parse($string)
    {
        $string = str_replace(["\r\n", "\n", "\r"], ';', $string);
        $string = preg_replace('/\s+/', '', $string);
        $lines  = explode(';', $string);

        $newRules = array();
        foreach ($lines as $oneLine) {
            if (( $new = $this->tryParseOne($oneLine) ) !== false) {
                $newRules[] = $new;
            }
        }

        $this->rules = array_merge($this->rules, $newRules);

        return $this;
    }

    public function tryParseOne($oneLine)
    {

        if (mb_strlen($oneLine) < 5) { // minimal rule is: (0):a
            return false;
        }

        if (mb_substr($oneLine, 0, 1) !== '(') { // should begin with (
            return false;
        }

        $matches  = array();
        //                           1    2    3     4    5    6      7
        $matchNum = preg_match('/^\((-)?(\d+)?(\.\.)?(-)?(\d+)?(!)?\):(.*)$/', $oneLine, $matches);

        if ($matchNum <= 0) {
            return false;
        }

        list(, $startSign, $start, $dots, $endSign, $end, $undoneOnly, $recipients) = $matches;

        $r = new NotificationRule();
        $r->targets = explode(',', $recipients);

        if ($undoneOnly == '!') {
            $r->onlyIfUnsolved = true;
        }

        $start = ($start == "") ? INF : intval($start);
        $end = ($end == "") ? (-1 * INF) : intval($end);

        if ($startSign == '-') {
            $start *= -1;
        }
        if ($endSign == '-') {
            $end *= -1;
        }

        if ($dots !== '..') {
            // its not interval
            $end = $start;
        }

        $r->intervalStart = $start;
        $r->intervalEnd = $end;

        var_dump($r);
        return $r;


    }

    public function isToday($daysToEvent, $isSolved)
    {
        $res = 0;

        foreach ($this->rules as $rule) {
            if ($rule->isConditionMet($daysToEvent, $isSolved)) {
                $res++;
            }
        }

        if ($res > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function whoToNotify($daysToEvent, $isSolved)
    {
        $recipients = [];

        foreach ($this->rules as $rule) {
            if ($rule->isConditionMet($daysToEvent, $isSolved)) {
                $recipients = array_merge($recipients, $rule->targets);
            }
        }

        $recipients = array_values(array_unique($recipients));

        if ($this->recipientReducer !== null) {
            $recipients = array_reduce($recipients, $this->recipientReducer, []);
        }

        return $recipients;

    }

    public function clear()
    {
        $this->rules = [];
        return $this;
    }

    public function setRecipientReducer(callable $reducer)
    {
        $this->recipientReducer = $reducer;

        return $this;
    }

} 