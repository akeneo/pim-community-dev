<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Connector\Writer\File\Csv;

use Akeneo\ReferenceEntity\Infrastructure\Connector\Writer\File\AbstractRecordWriter;

final class RecordWriter extends AbstractRecordWriter
{
    /**
     * {@inheritdoc}
     */
    protected function getWriterConfiguration(): array
    {
        $parameters = $this->stepExecution->getJobParameters();

        return [
            'type' => 'csv',
            'fieldDelimiter' => $parameters->get('delimiter'),
            'fieldEnclosure' => $parameters->get('enclosure'),
            'shouldAddBOM' => false,
        ];
    }
}
