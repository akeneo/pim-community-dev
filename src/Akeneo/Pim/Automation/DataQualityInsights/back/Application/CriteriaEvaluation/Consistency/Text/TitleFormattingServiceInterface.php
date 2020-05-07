<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency\Text;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToProvideATitleSuggestion;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductTitle;

interface TitleFormattingServiceInterface
{
    /**
     * @throws UnableToProvideATitleSuggestion
     */
    public function format(ProductTitle $title): ProductTitle;
}
