<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFileTemplateInformationHandler
{
    const ROW_NUMBER_DISPLAYED_IN_PREVIEW = 20;

    public function __construct(
        private XlsxFileReaderFactoryInterface $xlsxFileReaderFactory,
    ) {
    }

    public function handle(GetFileTemplateInformationQuery $getFileTemplateQuery): FileTemplateInformationResult
    {
        $fileReader = $this->xlsxFileReaderFactory->create($getFileTemplateQuery->fileKey);
        $rows = $fileReader->readRows($getFileTemplateQuery->sheetName, 0, self::ROW_NUMBER_DISPLAYED_IN_PREVIEW);

        return FileTemplateInformationResult::create(
            $fileReader->getSheetNames(),
            $rows
        );
    }
}
