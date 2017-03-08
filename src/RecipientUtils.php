<?php


namespace JanDanielCz\Simona;


class RecipientUtils
{


    public static function groupRelMailPlainReducer($carry, $recipient)
    {
        foreach (['group', 'rel', 'mail', 'plain'] as $type) {
            if (!isset($carry[$type])) {
                $carry[$type] = [];
            }
        }

        if (substr($recipient, 0, 1) == '@') {
            $carry['group'][] = substr($recipient, 1);
            return $carry;
        }

        $matches = [];
        if (preg_match('#^\[(.*)\]$#', $recipient, $matches) == 1) {
            var_dump($matches);
            $carry['rel'][] = $matches[1];
            return $carry;
        }

        if (preg_match('#.+@.+\..+#', $recipient) == 1) {
            $carry['mail'][] = $recipient;
            return $carry;
        }

        $carry['plain'][] = $recipient;
        return $carry;


    }
}