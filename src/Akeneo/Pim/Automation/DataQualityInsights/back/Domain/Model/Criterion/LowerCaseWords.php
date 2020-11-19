<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Criterion;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Rate;

final class LowerCaseWords
{
    public const CRITERION_CODE = 'consistency_textarea_lowercase_words';

    public const CRITERION_COEFFICIENT = 1;

    private const POINTS_TO_SUBTRACT_PER_ERROR = 24;

    /** @var CriterionCode */
    private $code;

    public function __construct()
    {
        $this->code = new CriterionCode(self::CRITERION_CODE);
    }

    public function getCode(): CriterionCode
    {
        return $this->code;
    }

    public function evaluate(string $productValue): Rate
    {
        $matches = [];
        preg_match_all('~(?:(?:^\s*)|(?:[\.|\?|\!\:]\s+))[a-z]~', $productValue, $matches);

        $nbErrors = empty($matches) ? 0 : count($matches[0]);
        $score = max(0, 100 - $nbErrors * self::POINTS_TO_SUBTRACT_PER_ERROR);

        return new Rate($score);
    }
}
