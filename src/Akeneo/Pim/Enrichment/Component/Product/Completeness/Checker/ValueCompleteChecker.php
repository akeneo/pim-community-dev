<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Completeness\Checker;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Chained checker that contains all the product value completeness checkers.
 * It's the front checker that should be used to determine if a value is complete on a given couple channel/locale.
 *
 * This checkers supports values that are compatible with the given couple locale/scope.
 * Then it delegates to the internal checkers the responsibility to check the completeness
 * depending on the value's attribute type.
 *
 * You **have to** call "supportsValue" before calling "isComplete".
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValueCompleteChecker implements ValueCompleteCheckerInterface
{
    /** @var ValueCompleteCheckerInterface[] */
    protected $productValueCheckers = [];

    /** @var LruArrayAttributeRepository */
    protected $attributeRepository;

    public function __construct(LruArrayAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isComplete(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        foreach ($this->productValueCheckers as $productValueChecker) {
            if ($productValueChecker->supportsValue($value, $channel, $locale)) {
                return $productValueChecker->isComplete($value, $channel, $locale);
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsValue(
        ValueInterface $value,
        ChannelInterface $channel,
        LocaleInterface $locale
    ) {
        if (null !== $value->getScopeCode() && $channel->getCode() !== $value->getScopeCode()) {
            return false;
        }

        if (null !== $value->getLocaleCode() && $locale->getCode() !== $value->getLocaleCode()) {
            return false;
        }

        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        if (null === $attribute) {
            return false;
        }

        if ($attribute->isLocaleSpecific() &&
            !in_array($locale->getCode(), $attribute->getAvailableLocaleCodes())

        ) {
            return false;
        }

        return true;
    }

    /**
     * @param ValueCompleteCheckerInterface $checker
     */
    public function addProductValueChecker(ValueCompleteCheckerInterface $checker)
    {
        $this->productValueCheckers[] = $checker;
    }
}
