<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteCheckerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

/**
 * Simple factory of "incomplete values" collection.
 *
 * @internal
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class IncompleteValueCollectionFactory
{
    /** @var ValueCompleteCheckerInterface */
    private $completeValueChecker;

    /**
     * @param ValueCompleteCheckerInterface $completeValueChecker
     */
    public function __construct(ValueCompleteCheckerInterface $completeValueChecker)
    {
        $this->completeValueChecker = $completeValueChecker;
    }

    /**
     * Create a collection of incomplete values depending of a collection of required values.
     * This method will create the incomplete values for the given couple $channel/$locale.
     *
     * @param RequiredValueCollection   $requiredValues
     * @param ChannelInterface          $channel
     * @param LocaleInterface           $locale
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @return IncompleteValueCollection
     */
    public function forChannelAndLocale(
        RequiredValueCollection $requiredValues,
        ChannelInterface $channel,
        LocaleInterface $locale,
        EntityWithValuesInterface $entityWithValues
    ): IncompleteValueCollection {
        $requiredValuesForChannelAndLocale = $requiredValues->filterByChannelAndLocale($channel, $locale);
        $incompleteValues = [];

        foreach ($requiredValuesForChannelAndLocale as $requiredValue) {
            if ($this->isValueMissingOrEmpty($requiredValue, $channel, $locale, $entityWithValues)) {
                $incompleteValues[] = $requiredValue;
            }
        }

        return new IncompleteValueCollection($incompleteValues);
    }

    /**
     * @param RequiredValue             $requiredValue
     * @param ChannelInterface          $channel
     * @param LocaleInterface           $locale
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @return bool
     */
    private function isValueMissingOrEmpty(
        RequiredValue $requiredValue,
        ChannelInterface $channel,
        LocaleInterface $locale,
        EntityWithValuesInterface $entityWithValues
    ) {
        $actualValue = $entityWithValues->getValues()->getByCodes(
            $requiredValue->attribute(),
            $requiredValue->channel(),
            $requiredValue->locale()
        );

        if (null === $actualValue) {
            return true;
        }

        if (!$this->completeValueChecker->isComplete($actualValue, $channel, $locale)) {
            return true;
        }

        return false;
    }
}
