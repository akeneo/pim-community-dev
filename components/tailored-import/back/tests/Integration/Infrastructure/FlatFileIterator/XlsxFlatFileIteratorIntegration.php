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

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\FlatFileIterator;

use Akeneo\Platform\TailoredImport\Infrastructure\FlatFileIterator\XlsxFlatFileIterator;
use Akeneo\Platform\TailoredImport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class XlsxFlatFileIteratorIntegration extends IntegrationTestCase
{
    public function it_returns_the_file_content()
    {

    }

    public function it_returns_the_correct_number_of_rows_when_iterating_over_a_xlsx_file()
    {
        $filePath = __DIR__ . '/../../../../../../../tests/fixtures/xlsx/products.xlsx';
        $iterator = $this->getFlatFileIterator($filePath);

        $this->assertSame(3, $iterator->count());
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getFlatFileIterator()
    {
        $rootDir = $this::getContainer()->getParameter('kernel.root_dir');

        return new XlsxFlatFileIterator(
            'xlsx',
            $rootDir . '/components/tailored-import/back/tests/Common/test_import.xlsx',
            [
                
            ]
        );
    }
}
