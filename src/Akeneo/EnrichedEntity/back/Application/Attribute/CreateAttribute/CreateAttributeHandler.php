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

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute;

use Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute\AttributeFactory\AttributeFactoryRegistryInterface;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

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

    public function __construct(
        AttributeFactoryRegistryInterface $attributeFactoryRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeFactoryRegistry = $attributeFactoryRegistry;
    }

    public function __invoke(AbstractCreateAttributeCommand $command): void
    {
        $identifier = $this->attributeRepository->nextIdentifier(
            EnrichedEntityIdentifier::fromString($command->enrichedEntityIdentifier),
            AttributeCode::fromString($command->code)
        );

        $attribute = $this->attributeFactoryRegistry->getFactory($command)->create($command, $identifier);
        $this->attributeRepository->create($attribute);
    }
}
