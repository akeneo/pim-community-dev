<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\FollowUp\Query;

use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCompletenessPerChannelAndLocaleInterface
{
    /**
     * Generate the completeness widget by searching numbers depending on locale code
     *
     * @param string $translationLocaleCode
     * @return CompletenessWidget
     */
    public function fetch(string $translationLocaleCode): CompletenessWidget;
}
