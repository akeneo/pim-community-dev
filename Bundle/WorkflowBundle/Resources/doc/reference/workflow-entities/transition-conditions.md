Transition Conditions
=====================

Table of Contents
-----------------
 - [Add Custom Condition](#add-custom-condition)
 - [And Condition](#and-condition)
 - [Or Condition](#or-condition)
 - [Not Condition](#not-condition)
 - [Equal To Condition](#equal-to-condition)
 - [Not Equal To Condition](#not-equal-to-condition)
 - [Blank Condition](#blank-condition)
 - [Not Blank Condition](#not-blank-condition)
 - [Greater Than Condition](#greater-than-condition)
 - [Greater Than Or Equal Condition](#greater-than-or-equal-condition)
 - [Less Than Condition](#less-than-condition)
 - [Less Than Or Equal Condition](#less-than-or-equal-condition)
 - [True Condition](#true-condition)
 - [False Condition](#false-condition)
 - [Configurable Condition](#configurable-condition)

Add Custom Condition
-------------------------

To add custom condition simply add a service to DIC with tag "oro_workflow.condition", for example:

```
parameters:
    oro_workflow.condition.blank.class: Oro\Bundle\WorkflowBundle\Model\Condition\Blank
services:
    oro_workflow.condition.blank:
        class: %oro_workflow.condition.blank.class%
        arguments: [@oro_workflow.context_accessor]
        tags:
            - { name: oro_workflow.condition, alias: blank|empty }
```

Symbol "|" in alias can be used to have several aliases. Note that service class must implement
Oro\Bundle\WorkflowBundle\Model\Condition\ConditionInterface.

And Condition
-------------

**Alias:** and

**Description:** Composite that uses logical AND operator to calculate result of all child conditions.

**Options:**
 - Requires at least one option
 - Each option must refer to some condition

**Configuration Example**

Is value of attribute "call_timeout" not blank AND equal to 20.
```
@and:
    - @not_blank: [$call_timeout]
    - @equals: [$call_timeout, 20]
```

Or Condition
------------

**Alias:** or

**Description:** Composite that uses logical OR operator to calculate result of all child conditions.

**Options:**
 - Requires at least one option
 - Each option must refer to some condition

**Configuration Example**

Is value of attribute "call_timeout" not blank OR equal to 20.
```
@or:
    - @blank: [$call_timeout]
    - @equals: [$call_timeout, 20]
```

Not Condition
-------------

**Alias:** not

**Description:** Negates condition that is passed in option.

**Options:**
 - Requires only one option
 - Option must be an instance of condition

**Configuration Example**

Is value of attribute "call_timeout" not blank.
```
@not:
    @blank: [$call_timeout]
```

Equal To Condition
------------------

**Aliases:** equal, eq

**Description:** Compares two values for equality.

**Options:**
 - Requires exactly 2 options
 - Option can be a property path or scalar value

**Configuration Example**

Is value of attribute "call_timeout" equal 60.
```
# Using associative option names
@equal:
    left: $call_timeout # A property path
    right: 60 # A scalar value

# Same as using non-associative options
@equal: [$call_timeout, 60]
```

Not Equal To Condition
----------------------

**Aliases:** not_equal, neq

**Description:** Compares two values for inequality.

**Options:**
 - Requires exactly 2 options
 - Option can be a property path or scalar value

**Configuration Example**

Is value of attribute "call_timeout" not equal 60.
```
# Using associative option names
@not_equal:
    left: $call_timeout # A property path
    right: 60 # A scalar value

# Same as using non-associative options
@not_equal: [$call_timeout, 60]
```

Blank Condition
---------------

**Aliases:** blank, empty

**Description:** Checks if value is blank. Check performed using expression ``'' === $value || null === $value``

**Options:**
 - Requires one option
 - Option can be a property path or scalar value

**Configuration Example**

Is property value of attribute "call_timeout" blank.
```
@blank:
    $call_timeout
```

Not Blank Condition
-------------------

**Aliases:** not_blank, not_empty

**Description:** Checks if value is not blank. Check performed using negation of Blank condition
and is can be expressed with next code: ``'' !== $value && null !== $value``

**Options:**
 - Requires one option
 - Option can be a property path or scalar value

**Configuration Example**

Is property value of attribute "call_timeout" not blank
```
@not_blank:
    $call_timeout
```

Greater Than Condition
----------------------

**Aliases:** greater, gt

**Description:** Checks if value is greater than value.

**Options:**
 - Requires exactly 2 options
 - Option can be a property path or scalar value

**Configuration Example**

Is property value of attribute "call_timeout" greater than 60
```
# Using associative option names
@greater:
    left: $call_timeout # A property path
    right: 60 # A scalar value

# Same as using non-associative options
@greater: [$call_timeout, 60]
```

Greater Than Or Equal Condition
-------------------------------

**Aliases:** greater_or_equal, gte, ge

**Description:** Checks if value is greater or equal than value.

**Options:**
 - Requires exactly 2 options
 - Option can be a property path or scalar value

**Configuration Example**

Is property value of attribute "call_timeout" greater or equal than 60
```
# Using associative option names
@greater_or_equal:
    left: $call_timeout # A property path
    right: 60 # A scalar value

# Same as using non-associative options
@greater_or_equal: [$call_timeout, 60]
```

Less Than Condition
-------------------

**Aliases:** less, lt

**Description:** Checks if value is less than value.

**Options:**
 - Requires exactly 2 options
 - Option can be a property path or scalar value

**Configuration Example**

Is property value of attribute "call_timeout" less than 60
```
# Using associative option names
@less:
    left: $call_timeout # A property path
    right: 60 # A scalar value

# Same as using non-associative options
@less: [$call_timeout, 60]
```

Less Than Or Equal Condition
----------------------------

**Aliases:** less_or_equal, lte, le

**Description:** Checks if value is less or equal than value.

**Options:**
 - Requires exactly 2 options
 - Option can be a property path or scalar value

**Configuration Example**

Is property value of attribute "call_timeout" less or equal than 60
```
# Using associative option names
@less_or_equal:
    left: $call_timeout # A property path
    right: 60 # A scalar value

# Same as using non-associative options
@less_or_equal: [$call_timeout, 60]
```

True Condition
--------------

**Alias:** true

**Description:** Always return TRUE. Can be useful for testing purposes.

**Options:** prohibited

**Configuration Example**
```
@true
```

False Condition
---------------

**Alias:** false

**Description:** Always return FALSE. Can be useful for testing purposes.

**Options:** prohibited

**Configuration Example**
```
@false
```

Configurable Condition
----------------------

**Alias:** configurable

**Description:** Uses Condition Assembler to assemble conditions from passed configuration.
This condition is NOT intended to be used in configuration of Workflow.
But it can be used to create condition based on configuration in runtime.

**Options:**
 - Valid configuration of conditions.

**Code Example**

Is value of attribute "call_timeout" not blank AND equal to 20.
```php
$configuration = array(
    '@and' => array(
        '@not_blank' => array('$call_timeout'),
        '@equal' => array('$call_timeout', 20)
    )
);
/** @var $conditionFactory \Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory */
$condition = $conditionFactory->create(Configurable::ALIAS', $configuration);

/** @var object $data */
$data->call_timeout = 20;

var_dump($condition->isAllowed($data)); // will output TRUE
```
