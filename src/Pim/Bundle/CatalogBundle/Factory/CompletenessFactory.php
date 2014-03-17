<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\Completeness;

/**
 * Simple factory to generate a Completeness object
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFactory
{
    /**
     * Generate a Completeness object from the parameters
     *
     * @param Channel $channel
     * @param Locale  $locale
     * @param int     $missingCount
     * @param int     $requiredCount
     *
     * @return Completeness
     */
    public function build(Channel $channel, Locale $locale, $missingCount, $requiredCount)
    {
        $completeness = new Completeness();

        $completeness->setChannel($channel);
        $completeness->setLocale($locale);

        $completeness->setMissingCount($missingCount);
        $completeness->setRequiredCount($requiredCount);
        $completeness->setRatio(($requiredCount - $missingCount) / $requiredCount * 100);

        return $completeness;
    }
}
