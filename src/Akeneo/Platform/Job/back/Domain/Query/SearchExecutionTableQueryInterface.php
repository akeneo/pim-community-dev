<?php

namespace Akeneo\Platform\Job\Domain\Query;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface SearchExecutionTableQueryInterface
{
    public function getPage(): int;

    public function getSize(): int;
}
