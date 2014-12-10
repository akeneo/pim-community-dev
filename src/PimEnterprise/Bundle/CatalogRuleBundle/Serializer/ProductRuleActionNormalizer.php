<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize and denormalize product rule actions.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleActionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @var string */
    protected $setValueActionClass;

    /** @var string */
    protected $copyValueActionClass;

    /**
     * @param string $setValueActionClass should implement \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface
     * @param string $copyValueActionClass should implement \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface
     */
    public function __construct($setValueActionClass, $copyValueActionClass)
    {
        $this->setValueActionClass = $setValueActionClass;
        $this->copyValueActionClass = $copyValueActionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (!isset($data['type'])) {
            throw new \LogicException(
                sprintf('Rule action "%s" has no type.', print_r($data, true))
            );
        }

        if (ProductSetValueActionInterface::TYPE === $data['type']) {
            return new $this->setValueActionClass($data);
        }

        if (ProductCopyValueActionInterface::TYPE === $data['type']) {
            return new $this->copyValueActionClass($data);
        }

        throw new \LogicException(
            sprintf('Rule action "%s" has an unknown type "%s".', print_r($data, true), $data['type'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            $type === 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction' ||
            $type === 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction'
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof ProductCopyValueActionInterface) {
            return $this->normalizeCopyValueAction($object);
        }

        if ($object instanceof ProductSetValueActionInterface) {
            return $this->normalizeSetValueAction($object);
        }

        throw new \LogicException(sprintf('Action of type "%s" can not be normalized.', get_class($object)));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductCopyValueActionInterface || $data instanceof ProductSetValueActionInterface;
    }

    /**
     * @param ProductSetValueActionInterface $action
     *
     * @return array
     */
    protected function normalizeSetValueAction(ProductSetValueActionInterface $action)
    {
        $data = [];
        $data['type'] = ProductSetValueActionInterface::TYPE;

        if (null !== $action->getField()) {
            $data['field'] = $action->getField();
        }
        if (null !== $action->getValue()) {
            $data['value'] = $action->getValue();
        }
        if (null !== $action->getLocale()) {
            $data['locale'] = $action->getLocale();
        }
        if (null !== $action->getScope()) {
            $data['scope'] = $action->getScope();
        }

        return $data;
    }

    /**
     * @param ProductCopyValueActionInterface $action
     *
     * @return array
     */
    protected function normalizeCopyValueAction(ProductCopyValueActionInterface $action)
    {
        $data = [];
        $data['type'] = ProductCopyValueActionInterface::TYPE;
        if (null !== $action->getFromField()) {
            $data['from_field'] = $action->getFromField();
        }
        if (null !== $action->getToField()) {
            $data['to_field'] = $action->getToField();
        }
        if (null !== $action->getFromLocale()) {
            $data['from_locale'] = $action->getFromLocale();
        }
        if (null !== $action->getToLocale()) {
            $data['to_locale'] = $action->getToLocale();
        }
        if (null !== $action->getFromScope()) {
            $data['from_scope'] = $action->getFromScope();
        }
        if (null !== $action->getToScope()) {
            $data['to_scope'] = $action->getToScope();
        }

        return $data;
    }
}
