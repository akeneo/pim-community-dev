<?php

namespace Pim\Bundle\CatalogBundle\Updater\Copier;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Copy a text value in many products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextValueCopier implements CopierInterface
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
    public function copyValue(array $products, $sourceField, $destinationField, array $context = [])
    {
        $attributeSource = $this->attributeRepository->findOneByCode($sourceField);
        if (!$attributeSource) {
            throw new \LogicException(sprintf('Attribute "%s" not exists', $sourceField));
        }
        $attributeDest = $this->attributeRepository->findOneByCode($destinationField);
        if (!$attributeDest) {
            throw new \LogicException(sprintf('Attribute "%s" not exists', $destinationField));
        }

        $context = $this->validateContext($attributeSource, $attributeDest, $context);

        $fromLocale = ($attributeSource->isLocalizable()) ? $context['from_locale'] : null;
        $fromScope = ($attributeSource->isScopable()) ? $context['from_scope'] : null;
        $toLocale = ($attributeDest->isLocalizable()) ? $context['to_locale'] : null;
        $toScope = ($attributeDest->isScopable()) ? $context['to_scope'] : null;

        foreach ($products as $product) {
            // from value
            $fromValue = $product->getValue($sourceField, $fromLocale, $fromScope);
            $fromData = (null === $fromValue) ? '' : $fromValue->getData();
            // to value
            $toValue = $product->getValue($destinationField, $toLocale, $toScope);
            if (null === $toValue) {
                // TODO : not sure about the relevancy of product builder for this kind of operation
                $toValue = $this->productBuilder->addProductValue($product, $attributeDest, $toLocale, $toScope);
            }
            $toValue->setData($fromData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($sourceField, $destinationField)
    {
        $types = ['pim_catalog_text', 'pim_catalog_textarea'];

        return in_array($sourceField, $types) && in_array($destinationField, $types);
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
     * @param AttributeInterface $attributeSource
     * @param AttributeInterface $attributeDest
     * @param array              $context
     *
     * @throws Symfony\Component\OptionsResolver\Exception\ExceptionInterface
     *
     * @return array
     */
    protected function validateContext(
        AttributeInterface $attributeSource,
        AttributeInterface $attributeDest,
        array $context
    ) {
        // TODO check the existence of locale and scope used as options
        $resolver = new OptionsResolver();
        $required = [];
        if ($attributeSource->isLocalizable()) {
            $required[] = 'from_locale';
        }
        if ($attributeDest->isLocalizable()) {
            $required[] = 'to_locale';
        }
        if ($attributeSource->isScopable()) {
            $required[] = 'from_scope';
        }
        if ($attributeDest->isScopable()) {
            $required[] = 'to_scope';
        }
        $resolver->setRequired($required);

        return $resolver->resolve($context);
    }
}
