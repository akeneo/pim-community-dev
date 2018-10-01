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

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\AttributeExistsInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAttributeExists implements AttributeExistsInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withIdentifier(AttributeIdentifier $identifier): bool
    {
        $attributes = $this->attributeRepository->getAttributes();
        $found = false;

        foreach ($attributes as $attribute) {
            if ($attribute->getIdentifier()->equals($identifier)) {
                $found = true;
            }
        }

        return $found;
    }

    public function withReferenceEntityAndCode(ReferenceEntityIdentifier $referenceEntityIdentifier, AttributeCode $attributeCode): bool
    {
        $attributes = $this->attributeRepository->getAttributes();
        $found = false;

        foreach ($attributes as $attribute) {
            $sameReferenceEntity = $attribute->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier);
            $sameCode = $attribute->getCode()->equals($attributeCode);

            if ($sameReferenceEntity && $sameCode) {
                $found = true;
            }
        }

        return $found;
    }

    public function withReferenceEntityIdentifierAndOrder(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeOrder $order
    ): bool {
        $attributes = $this->attributeRepository->getAttributes();
        foreach ($attributes as $attribute) {
            if ((string) $referenceEntityIdentifier === (string) $attribute->getReferenceEntityIdentifier() &&
                $attribute->hasOrder($order)
            ) {
                return true;
            }
        }

        return false;
    }
}
