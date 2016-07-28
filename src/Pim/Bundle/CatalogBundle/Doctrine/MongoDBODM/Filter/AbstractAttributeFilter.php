<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\ProductQueryUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Query\Filter\AttributeFilterInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Abstract MongoDB attribute filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeFilter extends AbstractFilter implements AttributeFilterInterface
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var string[] */
    protected $supportedAttributeTypes;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param LocaleRepositoryInterface  $localeRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        return $this->supportedAttributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributeTypes);
    }

    /**
     * Gets the normalized fields from an attribute.
     *
     * If catalog has 2 active channels (mobile and ecommerce) and 2 active scopes (en_US and fr_FR):
     * - Providing attribute + en_US + mobile will return:
     *    ['attribute_code-en_US-mobile']
     * - Providing attribute + en_US and no scope will return:
     *    [
     *        'attribute_code-en_US-mobile',
     *        'attribute_code-en_US-ecommerce',
     *    ]
     * - Providing attribute + mobile and no locale will return:
     *    [
     *        'attribute_code-en_US-mobile',
     *        'attribute_code-fr_FR-mobile',
     *    ]
     * - Providing only attribute will return:
     *    [
     *        'attribute_code-en_US-mobile',
     *        'attribute_code-fr_FR-mobile',
     *        'attribute_code-en_US-ecommerce',
     *        'attribute_code-fr_FR-ecommerce',
     *    ]
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return array
     */
    protected function getNormalizedValueFieldsFromAttribute(AttributeInterface $attribute, $locale, $scope)
    {
        if (null !== $locale) {
            $locales = [$locale];
        } else {
            $locales = $this->localeRepository->getActivatedLocaleCodes();
        }

        if (null !== $scope) {
            $scopes = [$scope];
        } else {
            $scopes = $this->channelRepository->getChannelCodes();
        }

        $fields = [];

        foreach ($locales as $locale) {
            foreach ($scopes as $scope) {
                $fields[] = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $locale, $scope);
            }
        }

        return $fields;
    }
}
