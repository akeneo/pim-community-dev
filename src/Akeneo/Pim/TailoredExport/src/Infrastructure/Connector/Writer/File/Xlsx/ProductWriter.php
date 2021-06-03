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
namespace Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File\Xlsx;

use Akeneo\Pim\TailoredExport\Infrastructure\Connector\Writer\File\AbstractItemMediaWriter;
use Box\Spout\Writer\WriterInterface;
use Symfony\Component\Filesystem\Filesystem;

class ProductWriter extends AbstractItemMediaWriter
{
    private WriterInterface $writer;

    public function __construct(Filesystem $localFileSystem, WriterInterface $writer)
    {
        parent::__construct($localFileSystem);
        $this->writer = $writer;
    }

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * @return array<string, mixed>
     * {@inheritdoc}
     */
    protected function getWriterConfiguration(): array
    {
        return ['type' => 'xlsx'];
    }
}
