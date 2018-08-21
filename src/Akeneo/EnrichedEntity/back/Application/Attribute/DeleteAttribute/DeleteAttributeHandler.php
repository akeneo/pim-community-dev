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

namespace Akeneo\EnrichedEntity\Application\Attribute\DeleteAttribute;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\EnrichedEntity\Domain\Repository\EnrichedEntityRepositoryInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAttributeHandler
{
    /** @var EnrichedEntityRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(DeleteAttributeCommand $deleteAttributeCommand): void
    {
        $identifier = AttributeIdentifier::create(
            $deleteAttributeCommand->identifier['enrichedEntityIdentifier'],
            $deleteAttributeCommand->identifier['identifier']
        );

        $this->attributeRepository->deleteByIdentifier($identifier);
    }
}
