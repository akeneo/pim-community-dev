<?php

declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\Writer\File;

use Akeneo\Tool\Component\Batch\Item\PausableWriterInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class XlsxUserWriter extends AbstractUserWriter implements PausableWriterInterface
{
    protected function getWriterConfiguration(): array
    {
        return ['type' => 'xlsx'];
    }

    public function getState(): array
    {
        return [];
    }
}
