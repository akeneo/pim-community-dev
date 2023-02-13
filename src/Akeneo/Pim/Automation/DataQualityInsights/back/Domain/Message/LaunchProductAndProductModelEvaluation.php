<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Message;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductModelIdCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluation
{
    /**
     * @param string[] $criteriaToEvaluate
     */
    public function __construct(
        public readonly \DateTimeImmutable $messageCreatedAt,
        public readonly ProductUuidCollection $productUuids,
        public readonly ProductModelIdCollection $productModelIds,
        public readonly array $criteriaToEvaluate
    ) {
        Assert::allString($this->criteriaToEvaluate);
    }
}
