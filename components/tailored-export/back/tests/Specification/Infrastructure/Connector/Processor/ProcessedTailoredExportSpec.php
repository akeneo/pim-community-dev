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

namespace Specification\Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor;

use Akeneo\Platform\TailoredExport\Application\ExtractMedia\ExtractedMedia;
use Akeneo\Platform\TailoredExport\Infrastructure\Connector\Processor\ProcessedTailoredExport;
use PhpSpec\ObjectBehavior;

class ProcessedTailoredExportSpec extends ObjectBehavior
{
    public function it_is_initializable(): void
    {
        $mappedProducts = $this->getFakeMappedProducts();
        $filesToExport = $this->getFakeFilesToExport();

        $this->beConstructedWith($mappedProducts, $filesToExport);

        $this->shouldHaveType(ProcessedTailoredExport::class);
    }

    public function it_returns_the_mapped_products()
    {
        $mappedProducts = $this->getFakeMappedProducts();
        $filesToExport = $this->getFakeFilesToExport();

        $this->beConstructedWith($mappedProducts, $filesToExport);

        $this->getItems()->shouldReturn($mappedProducts);
    }

    public function it_returns_the_files_to_export()
    {
        $mappedProducts = $this->getFakeMappedProducts();
        $filesToExport = $this->getFakeFilesToExport();

        $this->beConstructedWith($mappedProducts, $filesToExport);

        $this->getExtractedMediaCollection()->shouldReturn($filesToExport);
    }

    private function getFakeMappedProducts(): array
    {
        $mappedProducts = [];
        $mappedProducts['target 1'] = 'a_value another_one';
        $mappedProducts['target 2'] = 'foo bar';

        return $mappedProducts;
    }

    private function getFakeFilesToExport(): array
    {
        $filesToExport = [];
        $filesToExport[] = new ExtractedMedia('a_key', 'catalog', 'a_path_to_file');
        $filesToExport[] = new ExtractedMedia('another_key', 'catalog', 'another_path_to_file');

        return $filesToExport;
    }
}
