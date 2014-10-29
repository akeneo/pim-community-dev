<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;

/**
 * Sets a text value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextValueSetter implements SetterInterface
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var ProductBuilder */
    protected $productBuilder;

    /**
     * @param AttributeRepository $repository
     * @param ProductBuilder      $builder
     */
    public function __construct(AttributeRepository $repository, ProductBuilder $builder)
    {
        $this->attributeRepository = $repository;
        $this->productBuilder = $builder;
    }

    /**
     * {@inheritdoc}
     *
     * TODO : first draft, lot of re-work / discuss to have here, about validation and concern
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null)
    {
        $attribute = $this->attributeRepository->findOneByCode($field);
        if (!$attribute) {
            throw new \LogicException(sprintf('Attribute "%s" not exists', $field));
        }

        $this->validateData($data);
        $this->validateContext($attribute, $locale, $scope);

        $locale = ($attribute->isLocalizable()) ? $locale : null;
        $scope = ($attribute->isScopable()) ? $scope : null;

        foreach ($products as $product) {
            $value = $product->getValue($field, $locale, $scope);
            if (null === $value) {
                // TODO : not sure about the relevancy of product builder for this kind of operation
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }
            $value->setData($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($field)
    {
        $types = ['pim_catalog_text', 'pim_catalog_textarea'];

        return in_array($field, $types);
    }

    /**
     * Validate the data
     *
     * @param string $data
     *
     * @throws \LogicException
     */
    protected function validateData($data)
    {
        if (!is_string($data)) {
            throw new \LogicException('A string is expected');
        }
    }

    /**
     * Validate the context
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @throws \LogicException
     */
    protected function validateContext(AttributeInterface $attribute, $locale, $scope)
    {
        // TODO check the existence of locale and scope used as options
        if ($attribute->isLocalizable() && $locale === null) {
            throw new \LogicException(sprintf('A locale is expected for field %s', $attribute->getCode()));
        }
        if ($attribute->isScopable() && $scope === null) {
            throw new \LogicException(sprintf('A scope is expected for field %s', $attribute->getCode()));
        }
    }
}
