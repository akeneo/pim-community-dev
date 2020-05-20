<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\ErrorManagement;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ErrorTypes
{
    const TECHNICAL = 'technical';
    const BUSINESS = 'business';

    public static function getAll(): array
    {
        return [self::BUSINESS, self::TECHNICAL];
    }
}
