<?php

namespace Akeneo\Platform\Job\Application\SearchJobExecution;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindJobTypesInterface
{
    /**
     * @return string[]
     */
    public function visible(): array;
}
