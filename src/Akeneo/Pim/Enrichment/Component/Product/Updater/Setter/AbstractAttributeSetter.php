<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
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
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getType(), $this->supportedTypes);
    }
}
