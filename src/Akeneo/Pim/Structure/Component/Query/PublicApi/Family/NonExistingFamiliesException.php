<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\Family;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class NonExistingFamiliesException extends \RuntimeException
{
    public function __construct(array $nonExistingFamilyCodes)
    {
        parent::__construct(sprintf("The following family codes do not exist: %s", implode(", ", $nonExistingFamilyCodes)));
    }
}
