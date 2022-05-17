<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation;

use Akeneo\Platform\TailoredImport\Domain\Query\Filesystem\XlsxFileReaderFactoryInterface;

final class GetFileTemplateInformationHandler
{
    public const ROW_NUMBER_DISPLAYED_IN_PREVIEW = 20;

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
            $rows,
        );
    }
}
