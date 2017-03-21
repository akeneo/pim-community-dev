<?php

namespace Pim\Component\Catalog\Updater\Adder;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract adder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeAdder implements AttributeAdderInterface
{
    /** @var array */
    protected $supportedTypes = [];

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param ProductBuilderInterface  $productBuilder
     */
    public function __construct(ProductBuilderInterface $productBuilder)
    {
        $this->productBuilder = $productBuilder;

        $this->resolver = new OptionsResolver();
        $this->resolver->setRequired(['locale', 'scope']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(AttributeInterface $attribute)
    {
        return $this->supportsAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedTypes);
    }
}
