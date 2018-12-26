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

namespace Akeneo\ReferenceEntity\Integration\Connector\Collection;

use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;

class CreateOrUpdateAttributeContext implements Context
{
    private const REQUEST_CONTRACT_DIR = 'Attribute/Connector/Collect/';

    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /**
     * @param ReferenceEntityRepositoryInterface $referenceEntityRepository
     */
    public function __construct(ReferenceEntityRepositoryInterface $referenceEntityRepository)
    {
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    /**
     * @Given the Color reference entity existing both in the ERP and in the PIM
     */
    public function theColorReferenceEntityExistingBothInTheErpAndInThePim()
    {
        $referenceEntity = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('color'),
            [],
            Image::createEmpty()
        );

        $this->referenceEntityRepository->create($referenceEntity);
    }
}
