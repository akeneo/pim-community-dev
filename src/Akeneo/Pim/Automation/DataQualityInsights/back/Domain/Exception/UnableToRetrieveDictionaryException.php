<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;

final class UnableToRetrieveDictionaryException extends \Exception
{
    public function __construct(LanguageCode $languageCode, string $extraMessage = '')
    {
        parent::__construct(
            sprintf(
                'Unable to retrieve the dictionary for the language "%s". %s', $languageCode, $extraMessage
            )
        );
    }
}
