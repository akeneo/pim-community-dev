<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\EntityWithFamily;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ValueInterface;

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
     * @param RequiredValueCollectionInterface $requiredValues
     * @param ChannelInterface                 $channel
     * @param LocaleInterface                  $locale
     * @param EntityWithValuesInterface        $entityWithValues
     *
     * @return IncompleteValueCollectionInterface
     */
    public function forChannelAndLocale(
        RequiredValueCollectionInterface $requiredValues,
        ChannelInterface $channel,
        LocaleInterface $locale,
        EntityWithValuesInterface $entityWithValues
    ): IncompleteValueCollectionInterface {

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
     * @param ValueInterface            $value
     * @param ChannelInterface          $channel
     * @param LocaleInterface           $locale
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @return bool
     */
    private function isValueMissingOrEmpty(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale,
        EntityWithValuesInterface $entityWithValues
    ) {
        $values = $entityWithValues->getValues();
        $actualValue = $values->getSame($value);

        if (null === $actualValue) {
            return true;
        }

        if (!$this->completeValueChecker->isComplete($actualValue, $channel, $locale)) {
            return true;
        }

        return false;
    }
}
