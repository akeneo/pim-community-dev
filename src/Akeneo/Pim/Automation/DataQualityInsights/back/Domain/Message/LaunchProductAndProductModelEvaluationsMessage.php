<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Message;

use Akeneo\Pim\Platform\Messaging\Domain\PimMessageInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LaunchProductAndProductModelEvaluationsMessage implements PimMessageInterface
{
    public function __construct(public readonly string $text)
    {
    }
}
