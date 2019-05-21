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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class InMemoryReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttribute implements ReferenceEntityIsLinkedToAtLeastOneReferenceEntityAttributeInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function isLinked(ReferenceEntityIdentifier $identifier): bool
    {
        foreach ($this->attributeRepository->getAttributes() as $attribute) {
            if ($attribute instanceof RecordAttribute || $attribute instanceof RecordCollectionAttribute) {
                if ($attribute->getRecordType()->equals($identifier)) {
                    return true;
                }
            }
        }

        return false;
    }
}
