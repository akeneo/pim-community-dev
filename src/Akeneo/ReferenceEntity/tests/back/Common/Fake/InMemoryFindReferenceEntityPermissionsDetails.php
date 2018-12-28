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

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordDetails;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\FindReferenceEntityPermissionsDetailsInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntityPermission\PermissionDetails;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityPermissionRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryFindReferenceEntityPermissionsDetails implements FindReferenceEntityPermissionsDetailsInterface
{
    /** @var ReferenceEntityPermissionRepositoryInterface */
    private $repository;

    public function __construct(ReferenceEntityPermissionRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return PermissionDetails[]
     */
    public function __invoke(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
    }
}
