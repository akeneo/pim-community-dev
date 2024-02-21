<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage;

use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TemplatedErrorMessageInterface
{
    public function getTemplatedErrorMessage(): TemplatedErrorMessage;
}
