<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File;

use Akeneo\Tool\Component\Connector\Writer\File\SpoutWriterFactory;
use OpenSpout\Writer\WriterInterface;

class FileWriterFactory
{
    public function __construct(
        private string $type,
    ) {
    }

    public function build(array $options): WriterInterface
    {
        return SpoutWriterFactory::create($this->type, $options);
    }
}
