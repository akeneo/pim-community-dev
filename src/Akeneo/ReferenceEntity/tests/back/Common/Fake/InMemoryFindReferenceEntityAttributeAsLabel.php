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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityNotFoundException;

class InMemoryFindReferenceEntityAttributeAsLabel implements FindReferenceEntityAttributeAsLabelInterface
{
    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    public function __construct(InMemoryReferenceEntityRepository $referenceEntityRepository)
    {
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsLabelReference
    {
        try {
            $referenceEntity = $this->referenceEntityRepository->getByIdentifier($referenceEntityIdentifier);
        } catch (ReferenceEntityNotFoundException $e) {
            return AttributeAsLabelReference::noReference();
        }

        return $referenceEntity->getAttributeAsLabelReference();
    }
}
