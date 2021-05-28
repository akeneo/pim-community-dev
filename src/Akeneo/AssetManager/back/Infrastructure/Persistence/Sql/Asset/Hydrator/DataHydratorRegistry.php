<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DataHydratorRegistry
{
    private array $dataHydrators;

    public function __construct()
    {
        $this->dataHydrators = [];
    }

    public function register(DataHydratorInterface $dataHydrator): void
    {
        $this->dataHydrators[] = $dataHydrator;
    }

    public function getHydrator(AbstractAttribute $attribute): DataHydratorInterface
    {
        foreach ($this->dataHydrators as $dataHydrator) {
            if ($dataHydrator->supports($attribute)) {
                return $dataHydrator;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'There was no data hydrator found for the given attribute "%s"',
                $attribute->getIdentifier()
            )
        );
    }
}
