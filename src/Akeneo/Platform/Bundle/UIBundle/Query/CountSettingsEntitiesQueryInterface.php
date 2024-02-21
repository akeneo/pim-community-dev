<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\UIBundle\Query;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountSettingsEntitiesQueryInterface
{
    /**
     * @return array ["entity_name" => count]
     */
    public function execute(): array;
}
