<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * The PresenterRegistry registers the presenters to display attribute values readable information. The matching
 * presenters are returned from an attribute type or an option name.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PresenterRegistry implements PresenterRegistryInterface
{
    const TYPE_PRODUCT_VALUE = 'product_value';

    const TYPE_PRODUCT_FIELD = 'product_field';

    const TYPE_ATTRIBUTE_OPTION = 'attribute_option';

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var PresenterInterface[] */
    protected $presenters = [];

    /**
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function register(PresenterInterface $presenter, $type)
    {
        $this->presenters[$type][] = $presenter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPresenterByAttributeCode($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);
        if (null === $attribute) {
            return null;
        }

        $attributeType = $attribute->getType();
        if (null === $attributeType) {
            return null;
        }

        return $this->getPresenter($attributeType, self::TYPE_PRODUCT_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPresenterByAttributeType($attributeType)
    {
        return $this->getPresenter($attributeType, self::TYPE_PRODUCT_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionPresenter($optionName)
    {
        return $this->getPresenter($optionName, self::TYPE_ATTRIBUTE_OPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function getPresenterByFieldCode($code)
    {
        return $this->getPresenter($code, self::TYPE_PRODUCT_FIELD);
    }

    /**
     * Get a presenter supporting value and type
     *
     * @param string $value
     * @param string $type
     *
     * @return PresenterInterface|null
     */
    protected function getPresenter($value, $type)
    {
        if (isset($this->presenters[$type])) {
            foreach ($this->presenters[$type] as $presenter) {
                if ($presenter->supports($value)) {
                    return $presenter;
                }
            }
        }

        return null;
    }
}
