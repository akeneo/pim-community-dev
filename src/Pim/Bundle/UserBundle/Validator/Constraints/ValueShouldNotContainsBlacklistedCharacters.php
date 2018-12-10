<?php
declare(strict_types=1);

namespace Pim\Bundle\UserBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ValueShouldNotContainsBlacklistedCharacters extends Constraint
{
    public $message = 'This value should not contains following characters: {{ items }}.';
}
