<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Query;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CreateJobInstanceInterface
{
    public function createJobInstance(array $params): int;
}
