<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

use Ramsey\Uuid\Uuid;

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
