<?php

require '../vendor/autoload.php';

use Tester\Assert;
use JanDanielCz\Simona\RulesCollection;

\Tester\Environment::setup();

$rc = new RulesCollection();

$rc->parse('(1):a;');
Assert::equal(['a'],$rc->whoToNotify(1, false));
Assert::equal(['a'],$rc->whoToNotify(1, true));

// on second parse run old rules should be appended
$rc->parse('(1):b;');
Assert::equal(['a','b'],$rc->whoToNotify(1, false));

// simple usage with multiple recipients
Assert::equal(
    ['a', 'b'],
    $rc->clear()->parse('(1):a,b;')->whoToNotify(1, false)
);
// same with space
Assert::equal(
    ['a', 'b'],
    $rc->clear()->parse('(1):a, b;')->whoToNotify(1, false)
);
// same with more types
Assert::equal(
    ['@admins', 'my@example.com', '[rel]', 'plain'],
    $rc->clear()->parse('(1): @admins, my@example.com, [rel], plain;')->whoToNotify(1, false)
);

Assert::equal(
    ['a', 'b'],
    $rc->clear()->parse('(1):a,b;')->whoToNotify(1, false)
);

// Usage with intervals
Assert::equal(
    ['a'],
    $rc->clear()->parse('(1..):a;')->whoToNotify(1, false)
);

Assert::equal(
    ['a'],
    $rc->clear()->parse('(4..1):a;')->whoToNotify(3, false)
);

Assert::equal(
    ['a'],
    $rc->clear()->parse('(1..):a;')->whoToNotify(-3, true)
);

Assert::equal(
    [],
    $rc->clear()->parse('(1..):a;')->whoToNotify(2, true)
);

Assert::equal(
    [],
    $rc->clear()->parse('(..1):a;')->whoToNotify(-1, true)
);

Assert::equal(
    [],
    $rc->clear()->parse('(1..!):a;')->whoToNotify(-2, true)
);

Assert::equal(
    ['a'],
    $rc->clear()->parse('(..):a;')->whoToNotify(-2, true)
);

Assert::equal(
    [],
    $rc->clear()->parse('(..!):a;')->whoToNotify(-2, true)
);



// Multiple rules for one task

$rules = [
    '(1):a,b;',
    '(2..0):a; comment',
    '(1!):c,a; //another comment'
];

Assert::equal(
    ['a', 'b', 'c'],
    $rc->clear()->parse(join(PHP_EOL, $rules))->whoToNotify(1, false)
);

Assert::equal(
    ['a', 'b'],
    $rc->clear()->parse(join("\n", $rules))->whoToNotify(1, true)
);

Assert::equal(
    ['a'],
    $rc->clear()->parse(join("\r\n", $rules))->whoToNotify(0, true)
);

// Recipient reducer

$rules = [
    '(1):[aRel],@bGroup;',
    '(2..0):a@mail.c; comment',
    '(1!):cPlain,a_plain; //another comment'
];

$rc->clear()
    ->parse(join(PHP_EOL, $rules))
    ->setRecipientReducer(['\JanDanielCz\Simona\RecipientUtils','groupRelMailPlainReducer']);

Assert::equal(
    [
        'group' => ['bGroup'],
        'rel' => ['aRel'],
        'mail' => ['a@mail.c'],
        'plain' => ['cPlain', 'a_plain'],
    ],
    $rc->whoToNotify(1, false)
);
