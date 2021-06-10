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

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;

class FileWriterFactory
{
    private array $options;
    private string $type;

    public function __construct(string $type, array $options = [])
    {
        $this->type = $type;
        $this->options = $options;
    }

    public function build(): WriterInterface
    {
        $writer = WriterFactory::create($this->type);
        foreach ($this->options as $name => $option) {
            $setter = 'set' . ucfirst($name);
            if (!method_exists($writer, $setter)) {
                throw new \InvalidArgumentException(
                    sprintf('Option "%s" does not exist in writer "%s"', $setter, get_class($writer))
                );
            }

            $writer->$setter($option);
        }

        return $writer;
    }
}
