<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception;

final class DictionaryNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            'Unable to retrieve the dictionary for desired locale. Either it is not generated or we are unable to retrieve it if from shared filesystem'
        );
    }
}
