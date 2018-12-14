<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\ReferenceEntity\Permission;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CanEditReferenceEntityQuery
{
    /** @var string */
    public $referenceEntityIdentifier;

    /** @var string */
    public $principalIdentifier;
}
