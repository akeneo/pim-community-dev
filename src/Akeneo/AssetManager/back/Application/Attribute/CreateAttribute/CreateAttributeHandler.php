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

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute;

use Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindAttributeNextOrderInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CreateAttributeHandler
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var AttributeFactoryRegistryInterface */
    private $attributeFactoryRegistry;

    /** @var FindAttributeNextOrderInterface */
    private $attributeNextOrder;

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
            ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier),
            AttributeCode::fromString($command->code)
        );

        $order = $this->attributeNextOrder->withReferenceEntityIdentifier(
            ReferenceEntityIdentifier::fromString($command->referenceEntityIdentifier)
        );

        $attribute = $this->attributeFactoryRegistry->getFactory($command)->create($command, $identifier, $order);
        $this->attributeRepository->create($attribute);
    }
}
