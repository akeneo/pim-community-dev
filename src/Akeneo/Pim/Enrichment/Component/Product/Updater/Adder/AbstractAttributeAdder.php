<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Adder;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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

    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /** @var OptionsResolver */
    protected $resolver;

    /**
     * @param EntityWithValuesBuilderInterface $entityWithValuesBuilder
     */
    public function __construct(EntityWithValuesBuilderInterface $entityWithValuesBuilder)
    {
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;

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
