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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\Csv;

use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Writer\File\AbstractItemMediaWriter;

class EntityWithValuesWriter extends AbstractItemMediaWriter
{
    protected function getWriterOptions(): array
    {
        return [
            'fieldDelimiter' => $this->getDelimiter(),
            'fieldEnclosure' => $this->getEnclosure(),
            'shouldAddBOM' => false,
        ];
    }

    private function getDelimiter(): string
    {
        $parameters = $this->getStepExecution()->getJobParameters();

        return $parameters->has('delimiter') ? (string) $parameters->get('delimiter') : ';';
    }

    private function getEnclosure(): string
    {
        $parameters = $this->getStepExecution()->getJobParameters();

        return $parameters->has('enclosure') ? (string) $parameters->get('enclosure') : '"';
    }
}
