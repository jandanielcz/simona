Simona
======

_Simple language used to specify when and to whom send notifications._

Purpose
-------

For one side project (project management/IS/KB mix) I needed flexible but simple way to configure
when should users get notifications for their tasks. 

For example:
* Notify responsible user one week and 3 days before if task is not done yet.
* If task is one day delayed notify supervisor.
* If task is more than 3 days delayed notify all admins.


Than I can every night check all recent and future task, process
these rules and send emails.

Examples
--------

Notify responsible user one week before:
```
(1):[responsible];
```

If task is one day delayed notify supervisor:
```
(-1!):[supervisor];
```

If task is more than 3 days delayed notify all admins.:
```
(-3..!):@admins;
```

We can combine rules to create simple escalation scenarios:
```
(7):[responsible];
(1!):[responsible];
(-1..!):[responsible];
(-2!):[supervisor],@admins;
(-3!):tom@example.org;
```

Lines not starting with `(` and space after trailing `;` can be used
for comments.
```
(7):[responsible];
(1!):[responsible];
(-1..!):[responsible];      keep bugging him :)
(-2!):[supervisor],@admins; 

Tom from example corp. wants to know about it since 2017-01. Adam.
(-3!):tom@example.org;
```

Rule elements
-------------
1. time window definition 
2. `!` send notifications only if task is unsolved
3. `:` separator
4. list of recipients separated by `,` for my usage i decided to go with
    * specific email `dan@example.org`
    * relation to task `[responsible]`
    * user group `@itdept`
    * other `plain_string` (not used)
5. trailing `;`

Each rule should be on separate line.

Errors
------

If line starts with `(` it is considered rule and invalid rules, should be reported
to user. Every other line is ignored in processing.

PHP Usage
---------

### Basic usage

```php
$rc = new RulesCollection();
$rc->parse('(1):a;');
$rc->whoToNotify(1, false); // $daysToTask, $isTaskCompleted?

// returns ['a']
```

### Usage with recipient types

```php
$rc = new RulesCollection();
$rc->parse('(1):[a],@allUsers;');
$rc->setRecipientReducer(['\JanDanielCz\Simona\RecipientUtils','groupRelMailPlainReducer']);
$rc->whoToNotify(1, false); // $daysToTask, $isTaskCompleted?

/* 
returns [
    'group' => ['allUsers'],
    'rel' => ['a'],
    'mail' => [],
    'plain' => []
    ];
*/
```

You can create your own reducer to sort recipients and introduce any special
recipients syntax.

Limitations
-----------

Spaces inside recipient definition are removed before parsing by
`preg_replace('/\s+/', '', $string)` pull request with better regex is welcomed.