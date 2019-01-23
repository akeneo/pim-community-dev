<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQuery;
use Akeneo\ReferenceEntity\Application\ReferenceEntityPermission\CanEditReferenceEntity\CanEditReferenceEntityQueryHandler;
use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditReferenceEntityQueryHandlerStub extends CanEditReferenceEntityQueryHandler
{
    private $isAllowed = true;

    public function __invoke(CanEditReferenceEntityQuery $query): bool
    {
        Assert::stringNotEmpty($query->referenceEntityIdentifier);
        Assert::stringNotEmpty($query->securityIdentifier);

        return $this->isAllowed;
    }

    public function forbid(): void
    {
        $this->isAllowed = false;
    }
}
