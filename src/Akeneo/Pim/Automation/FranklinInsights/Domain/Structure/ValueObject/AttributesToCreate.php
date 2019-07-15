<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\ValueObject;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class AttributesToCreate implements \IteratorAggregate
{
    /** @var array */
    private $attributesToCreate;

    public function __construct(array $attributesToCreate)
    {
        $this->validateAttributesToCreate($attributesToCreate);

        foreach ($attributesToCreate as $index => $attributeToCreate) {
            $attributesToCreate[$index] = [
                'franklinAttributeLabel' => new FranklinAttributeLabel($attributeToCreate['franklinAttributeLabel']),
                'franklinAttributeType' => new FranklinAttributeType($attributeToCreate['franklinAttributeType']),
            ];
        }
        $this->attributesToCreate = $attributesToCreate;
    }

    public function getIterator(): iterable
    {
        return new \ArrayIterator($this->attributesToCreate);
    }

    private function validateAttributesToCreate(array $attributesToCreate): void
    {
        foreach ($attributesToCreate as $attribute) {
            if (! array_key_exists('franklinAttributeLabel', $attribute)) {
                throw new \InvalidArgumentException('franklinAttributeLabel');
            }
            if (! array_key_exists('franklinAttributeType', $attribute)) {
                throw new \InvalidArgumentException('franklinAttributeType');
            }
        }
    }
}
