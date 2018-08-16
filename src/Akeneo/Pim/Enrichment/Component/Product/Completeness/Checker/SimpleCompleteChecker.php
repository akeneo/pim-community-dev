<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Check if a product value data is complete or not. This checker should be registered as the last one so that
 * other particular checker (metric, prices etc..) can be called before.
 *
 * This way we provide a simple way to check if a custom attribute type value is complete or not.
 * For more complex use case, a custom checker should be written.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal for internal use only, please use
 *           \Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker\ValueCompleteChecker
 *           to calculate the completeness on a product value
 */
class SimpleCompleteChecker implements ValueCompleteCheckerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        $data = $value->getData();

        if ((is_array($data) || $data instanceof \Countable) && 0 === count($data)) {
            return false;
        }

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $item) {
                if ($this->isScalarDataComplete($item)) {
                    return true;
                }
            }

            return false;
        }

        return $this->isScalarDataComplete($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        return true;
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    private function isScalarDataComplete($data)
    {
        if (null !== $data && '' !== $data) {
            return true;
        }

        return false;
    }
}
