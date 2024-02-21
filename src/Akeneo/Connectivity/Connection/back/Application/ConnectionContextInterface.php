<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application;

use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConnectionContextInterface
{
    public function getConnection(): ?Connection;

    public function isCollectable(): bool;

    public function areCredentialsValidCombination(): bool;
}
