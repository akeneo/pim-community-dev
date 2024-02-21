<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Settings\Service;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Read\Client;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateClientInterface
{
    public function execute(string $label): Client;
}
