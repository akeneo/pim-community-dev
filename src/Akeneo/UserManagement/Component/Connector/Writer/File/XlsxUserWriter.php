<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\File;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxUserWriter extends AbstractUserWriter
{
    protected function getWriterConfiguration(): array
    {
        return ['type' => 'xlsx'];
    }
}
