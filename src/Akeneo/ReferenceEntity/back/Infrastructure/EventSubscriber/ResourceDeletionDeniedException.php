<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\EventSubscriber;

/**
 * Deletion denied for a resource.
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ResourceDeletionDeniedException extends \LogicException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
