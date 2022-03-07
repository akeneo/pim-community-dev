<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation;

use Akeneo\Platform\TailoredImport\Application\ReadColumns\UuidGeneratorInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Column;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileHeader;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileHeaderCollection;
use Akeneo\Platform\TailoredImport\Domain\Model\File\FileStructure;
use Akeneo\Platform\TailoredImport\Domain\Model\Filesystem\Storage;
use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\CellsFormatter;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\FileIteratorFactory;
use Akeneo\Platform\TailoredImport\Infrastructure\Spout\XlsxFileReader;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFileTemplateInformationHandler
{
    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function handle(GetFileTemplateInformationQuery $getFileTemplateQuery): FileTemplateInformationResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getFileTemplateQuery->fileKey);
        $fileReader->selectSheet($getFileTemplateQuery->sheetName);
        $headerValues = $fileReader->readLine($getFileTemplateQuery->headerLine);

        return FileTemplateInformationResult::create(
            $fileReader->getSheetList(),
            $headerValues
        );
    }
}
