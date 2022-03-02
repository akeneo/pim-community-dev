<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UuidNotFoundException extends \LogicException
{
    public const MESSAGE = 'No uuid found';

    public function __construct()
    {
        parent::__construct(self::MESSAGE);
    }
}
