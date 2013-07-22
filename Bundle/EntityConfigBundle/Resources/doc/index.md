Configurable Entity Example
====================

For ability to configure an Entity it should be marked as Configurable.
For example:

<?php

namespace Acme\Bundle\DemoBundle\Entity;
...
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;

/**
 * ...
 * @Configurable
 */
class Account
{
...