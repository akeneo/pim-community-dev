<?php

declare(strict_types=1);

/*
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain\Event;

final class FileCannotBeExported
{
    public function __construct(private string $reason)
    {
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
