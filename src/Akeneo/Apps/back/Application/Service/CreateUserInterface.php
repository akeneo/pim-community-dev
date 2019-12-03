<?php
declare(strict_types=1);

namespace Akeneo\Apps\Application\Service;

use Akeneo\Apps\Domain\Model\Read\User;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateUserInterface
{
    public function execute(string $username, string $firstname, string $lastname): User;
}
