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

namespace Specification\Akeneo\Platform\TailoredExport\Application;

use Akeneo\Platform\TailoredExport\Application\FilePathGenerator;
use Akeneo\Platform\TailoredExport\Application\Query\Column\Column;
use Akeneo\Platform\TailoredExport\Application\Query\Column\ColumnCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Operation\OperationCollection;
use Akeneo\Platform\TailoredExport\Application\Query\Selection\File\FilePathSelection;
use Akeneo\Platform\TailoredExport\Application\Query\Source\AttributeSource;
use Akeneo\Platform\TailoredExport\Application\Query\Source\SourceCollection;
use Akeneo\Platform\TailoredExport\Domain\FileToExport;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\FileValue;
use Akeneo\Platform\TailoredExport\Domain\ValueCollection;
use PhpSpec\ObjectBehavior;

class FilePathGeneratorSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(FilePathGenerator::class);
    }

    public function it_extracts_file_to_exports(): void
    {
        $operationCollection = OperationCollection::create([]);
        $source = new AttributeSource(
            'pim_catalog_file',
            'a_code',
            null,
            null,
            $operationCollection,
            new FilePathSelection('an_attribute_code')
        );
        $column = new Column('target1', SourceCollection::create([$source]));

        $columnCollection = ColumnCollection::create(
            [$column]
        );

        $valueCollection = new ValueCollection();
        $fileValue = new FileValue(
            'an_id',
            'catalog',
            'a_filekey',
            'an_original_filename',
            null,
            null
        );
        $valueCollection->add(
            $fileValue,
            'a_code',
            null,
            null
        );

        $expectedFilesToExport = [];
        $expectedFilesToExport['a_filekey'] = new FileToExport(
            'a_filekey',
            'catalog',
            'files/an_id/an_attribute_code/an_original_filename'
        );

        $filesToExport = $this->extract($columnCollection, $valueCollection);
        $filesToExport->shouldHaveCount(1);
        $filesToExport->shouldBeLike($expectedFilesToExport);
    }
}
