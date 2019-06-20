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
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Asset\Value\EmptyData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueDataInterface;
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetCollectionDataHydrator implements DataHydratorInterface
{
    /** @var FindCodesByIdentifiersInterface */
    private $findCodesByIdentifiers;

    public function __construct(FindCodesByIdentifiersInterface $findCodesByIdentifiers)
    {
        $this->findCodesByIdentifiers = $findCodesByIdentifiers;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof AssetCollectionAttribute;
    }

    public function hydrate($normalizedData, AbstractAttribute $attribute): ValueDataInterface
    {
        $filteredAssetCodes = $this->findCodes($normalizedData);
        if (empty($filteredAssetCodes)) {
            return EmptyData::create();
        }

        return AssetCollectionData::createFromNormalize(array_values($filteredAssetCodes));
    }

    /**
     * This method also cleans missing assets (returns only existing ones)
     */
    private function findCodes(array $identifiers): array
    {
        return $this->findCodesByIdentifiers->find($identifiers);
    }
}
