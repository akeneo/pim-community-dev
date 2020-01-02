<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read\TextCheckResultCollection;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
interface TextChecker
{
    /**
     * @param string $text
     * @param string $locale
     * @return TextCheckResultCollection
     */
    public function check(string $text, string $locale): TextCheckResultCollection;
}
