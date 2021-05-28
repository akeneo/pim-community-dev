<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ValueHydratorRegistry implements ValueHydratorInterface
{
    private array $valueHydrators;

    public function __construct()
    {
        $this->valueHydrators = [];
    }

    public function register(ValueHydratorInterface $valueHydrator): void
    {
        $this->valueHydrators[] = $valueHydrator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AbstractAttribute $attribute): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($normalizedValue, AbstractAttribute $attribute, array $context = []): array
    {
        $valueHydrator = $this->findHydrator($attribute);

        if (null === $valueHydrator) {
            return $normalizedValue;
        }

        return $valueHydrator->hydrate($normalizedValue, $attribute, $context);
    }

    private function findHydrator(AbstractAttribute $attribute): ?ValueHydratorInterface
    {
        foreach ($this->valueHydrators as $valueHydrator) {
            if ($valueHydrator->supports($attribute)) {
                return $valueHydrator;
            }
        }

        return null;
    }
}
