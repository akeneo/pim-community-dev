<?php

namespace Pim\Component\Localization\Presenter;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * Class PresenterAttributeConverter
 *
 * Used to convert attribute values to be presented to user
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PresenterAttributeConverter
{
    /** @var PresenterRegistry */
    protected $presenterRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * @param PresenterRegistry            $presenterRegistry
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        PresenterRegistry $presenterRegistry,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->presenterRegistry   = $presenterRegistry;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Localize an attribute value
     *
     * @param string $code
     * @param mixed  $data
     * @param array  $options
     *
     * @return mixed
     */
    public function convertDefaultToLocalizedValue($code, $data, array $options = [])
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);
        if (null === $attribute) {
            return $data;
        }

        $attributeType = $attribute->getAttributeType();
        if (null === $attributeType) {
            return $data;
        }

        $presenter = $this->presenterRegistry->getPresenter($attributeType);
        if (null === $presenter) {
            return $data;
        }

        return $presenter->present($data, $options);
    }
}
