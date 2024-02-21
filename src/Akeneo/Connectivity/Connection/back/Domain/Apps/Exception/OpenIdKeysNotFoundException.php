<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Exception;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OpenIdKeysNotFoundException extends \Exception
{
    public const MESSAGE = 'No OpenId keys';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
