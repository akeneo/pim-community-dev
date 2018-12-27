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
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\GetAttributeIdentifierInterface;

class InMemoryGetAttributeIdentifier implements GetAttributeIdentifierInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function withReferenceEntityAndCode(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier
    {
        $attributes = $this->attributeRepository->getAttributes();

        foreach ($attributes as $attribute) {
            $sameReferenceEntity = $attribute->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier);
            $sameCode = $attribute->getCode()->equals($attributeCode);

            if ($sameReferenceEntity && $sameCode) {
                return $attribute->getIdentifier();
            }
        }

        throw new \LogicException(
            sprintf(
                'Attribute identifier not found for "%s" attribute code and "%s" reference entity identifier.',
                $attributeCode,
                $referenceEntityIdentifier
            )
        );
    }
}
