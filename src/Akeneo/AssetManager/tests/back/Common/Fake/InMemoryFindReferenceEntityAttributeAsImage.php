<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsImageInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;

class InMemoryFindReferenceEntityAttributeAsImage implements FindReferenceEntityAttributeAsImageInterface
{
    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    public function __construct(InMemoryReferenceEntityRepository $referenceEntityRepository)
    {
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsImageReference
    {
        try {
            $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        } catch (ReferenceEntityNotFoundException $e) {
            return AttributeAsImageReference::noReference();
        }

        return $referenceEntity->getAttributeAsImageReference();
    }
}
