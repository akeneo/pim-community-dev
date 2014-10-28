<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    public function setValue(array $products, $field, $data, array $context = [])
    {
        $attribute = $this->attributeRepository->findOneByCode($field);
        if (!$attribute) {
            throw new \LogicException(sprintf('Attribute "%s" not exists', $field));
        }

        $this->validateData($data);
        $context = $this->validateContext($attribute, $context);

        $locale = ($attribute->isLocalizable()) ? $context['locale'] : null;
        $scope = ($attribute->isScopable()) ? $context['scope'] : null;

        foreach ($products as $product) {
            $value = $product->getValue($attribute->getCode(), $locale, $scope);
            if (null === $value) {
                // TODO : not sure about the relevancy of product builder for this kind of operation
                $value = $this->productBuilder->addProductValue($product, $attribute, $locale, $scope);
            }
            $value->setData($data);
        }
    }

    /**
     * Supports the field
     *
     * @return true
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
     * @param array              $context
     *
     * @throws Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return array
     */
    protected function validateContext(AttributeInterface $attribute, array $context)
    {
        // TODO check the locale and scope exists
        $resolver = new OptionsResolver();
        $this->configureOptions($attribute, $resolver);

        return $resolver->resolve($context);
    }

    /**
     * @param AttributeInterface       $attribute
     * @param OptionsResolverInterface $resolver
     */
    protected function configureOptions(AttributeInterface $attribute, OptionsResolverInterface $resolver)
    {
        $required = [];
        if ($attribute->isLocalizable()) {
            $required[] = 'locale';
        }
        if ($attribute->isScopable()) {
            $required[] = 'scope';
        }
        $resolver->setRequired($required);
    }
}
