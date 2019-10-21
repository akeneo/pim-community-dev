<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Persistence\Repository;

use Akeneo\Apps\Domain\Model\ValueObject\AppId;
use Akeneo\Apps\Domain\Model\Write\App;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AppRepository
{
    public function create(App $app): void;

    public function fetchAll(): array;

    public function generateId(): AppId;
}
