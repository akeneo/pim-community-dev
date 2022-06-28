<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Bundle\ImportExportBundle\Domain;

use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\NoneStorage;
use Akeneo\Platform\Bundle\ImportExportBundle\Domain\Model\StorageInterface;

interface StorageHydratorInterface
{
    public function hydrate(array $normalizedStorage): StorageInterface|NoneStorage;

    public function supports(array $normalizedStorage): bool;
}
