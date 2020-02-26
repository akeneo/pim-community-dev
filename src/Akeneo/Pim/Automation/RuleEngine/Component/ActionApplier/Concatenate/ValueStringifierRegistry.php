<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\ActionApplier\Concatenate;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class ValueStringifierRegistry
{
    /** @var ValueStringifierInterface[] */
    private $stringifiers = [];

    public function __construct(iterable $stringifiers)
    {
        foreach ($stringifiers as $stringifier) {
            $this->addStringifier($stringifier);
        }
    }

    private function addStringifier(ValueStringifierInterface $stringifier): void
    {
        foreach ($stringifier->forAttributesTypes() as $attributeType) {
            $this->stringifiers[$attributeType] = $stringifier;
        }
    }

    public function getStringifier(string $attributeType): ?ValueStringifierInterface
    {
        return $this->stringifiers[$attributeType] ?? null;
    }
}
