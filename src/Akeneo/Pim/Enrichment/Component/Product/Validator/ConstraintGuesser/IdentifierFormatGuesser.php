<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesser;

use Akeneo\Pim\Enrichment\Component\Product\Validator\ConstraintGuesserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\Length;
use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\UniqueValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Guesser
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFormatGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AttributeInterface $attribute)
    {
        return $attribute->getType() === AttributeTypes::IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AttributeInterface $attribute)
    {
        return [
            new Regex([
                'pattern' => '/^(?!\s)[^,;]+(?<!\s)$/',
                'message' => 'regex.comma_or_semicolon_or_surrounding_space.message'
            ]),
            new Regex([
                'pattern' => '/^[^\r\n\f\v]+$/D',
                'message' => 'regex.line_break.message'
            ])
        ];
    }
}
