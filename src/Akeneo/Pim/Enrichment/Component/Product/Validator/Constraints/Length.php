<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Length as BaseLength;

class Length extends BaseLength
{
    /** @var string */
    public $maxMessage = 'The %attribute% attribute must not contain more than %limit% characters. The submitted value is too long.';

    /** @var string */
    public $attributeCode = '';
}
