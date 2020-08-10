<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception;

final class UnableToProvideATitleSuggestion extends \Exception
{
    public function __construct()
    {
        parent::__construct(
            'An error occurred while trying to provide a title suggestion.'
        );
    }
}
