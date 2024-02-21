<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CriterionEvaluationId
{
    /**
     * @var string
     */
    private $uuid;

    public function __construct(?string $uuid = null)
    {
        if (empty($uuid)) {
            $uuid = strval(Uuid::uuid4());
        }
        $this->uuid = $uuid;
    }

    public function __toString()
    {
        return strval($this->uuid);
    }
}
