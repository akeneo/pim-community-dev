<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\Application\FindJobType;

/**
 * @author Grégoire Houssard <gregoire.houssard@akeneo.com>
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface FindJobTypeInterface
{
    /**
     * @return string[]
     */
    public function visible(): array;
}
