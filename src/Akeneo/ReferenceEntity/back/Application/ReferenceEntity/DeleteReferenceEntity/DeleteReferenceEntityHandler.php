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

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteReferenceEntityHandler
{
    public function __construct(private ReferenceEntityRepositoryInterface $referenceEntityRepository)
    {
    }

    public function __invoke(DeleteReferenceEntityCommand $deleteReferenceEntityCommand): void
    {
        $identifier = ReferenceEntityIdentifier::fromString($deleteReferenceEntityCommand->identifier);

        $this->referenceEntityRepository->deleteByIdentifier($identifier);
    }
}
