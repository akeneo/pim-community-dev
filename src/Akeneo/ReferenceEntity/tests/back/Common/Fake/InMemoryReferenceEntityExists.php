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

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;

/**
 * Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryReferenceEntityExists implements ReferenceEntityExistsInterface
{
    /** @var InMemoryReferenceEntityRepository */
    private $referenceEntityRepository;

    public function __construct(InMemoryReferenceEntityRepository $referenceEntityRepository)
    {
        $this->referenceEntityRepository = $referenceEntityRepository;
    }

    public function withIdentifier(ReferenceEntityIdentifier $referenceEntityIdentifier): bool
    {
        return $this->referenceEntityRepository->hasReferenceEntity($referenceEntityIdentifier);
    }
}
