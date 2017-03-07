<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Abstract data setter.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractAttributeSetter implements AttributeSetterInterface
{
    /** @var string[] */
    protected $supportedTypes = [];

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param ProductBuilderInterface $productBuilder
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
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedTypes);
    }
}
