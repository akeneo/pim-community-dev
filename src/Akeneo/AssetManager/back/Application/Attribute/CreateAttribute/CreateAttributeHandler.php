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

namespace Akeneo\AssetManager\Application\Attribute\CreateAttribute;

use Akeneo\AssetManager\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributeHandler
{
    private AttributeRepositoryInterface $attributeRepository;

    private AttributeFactoryRegistryInterface $attributeFactoryRegistry;

    private FindAttributeNextOrderInterface $attributeNextOrder;

    public function __construct(
        AttributeFactoryRegistryInterface $attributeFactoryRegistry,
        AttributeRepositoryInterface $attributeRepository,
        FindAttributeNextOrderInterface $attributeNextOrder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactoryRegistry = $attributeFactoryRegistry;
        $this->attributeNextOrder = $attributeNextOrder;
    }

    public function __invoke(AbstractCreateAttributeCommand $command): void
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier),
            AttributeCode::fromString($command->code)
        );

        $order = $this->attributeNextOrder->withAssetFamilyIdentifier(
            AssetFamilyIdentifier::fromString($command->assetFamilyIdentifier)
        );

        $attribute = $this->attributeFactoryRegistry->getFactory($command)->create($command, $identifier, $order);
        $this->attributeRepository->create($attribute);
    }
}
