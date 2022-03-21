<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\GetFileTemplateInformation;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetFileTemplateInformationQuery
{
    public string $fileKey;

    public ?string $sheetName;

    public int $headerLine;
}
