<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Persistence\Repository;

use Akeneo\Apps\Domain\Model\Read\App as ReadApp;
use Akeneo\Apps\Domain\Model\Write\App as WriteApp;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AppRepository
{
    public function findOneByCode(string $code): ?ReadApp;

    public function create(WriteApp $app): void;

    public function fetchAll(): array;

    public function generateId(): string;
}
