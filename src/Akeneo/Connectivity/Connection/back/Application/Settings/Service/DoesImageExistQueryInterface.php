<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Service;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DoesImageExistQueryInterface
{
    public function execute(string $filePath): bool;
}
