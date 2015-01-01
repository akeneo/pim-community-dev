<?php

namespace Pim\Bundle\CatalogBundle\Remover;

use Akeneo\Component\Persistence\RemoverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base remover, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseRemover implements RemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $removedClass;

    /**
     * @param ObjectManager $manager
     * @param string        $removedClass
     */
    public function __construct(ObjectManager $objectManager, $removedClass)
    {
        $this->objectManager = $objectManager;
        $this->removedClass  = $removedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof $this->removedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->removedClass,
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = $this->resolveRemoveOptions($options);
        $this->objectManager->remove($object);

        if (true === $options['flush'] && true === $options['flush_only_object']) {
            $this->objectManager->flush($object);
        } elseif (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Resolve options for a single remove
     *
     * @param array $options
     *
     * @return array
     */
    protected function resolveRemoveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setOptional(['flush', 'flush_only_object']);
        $resolver->setAllowedTypes(
            [
                'flush' => 'bool',
                'flush_only_object' => 'bool'
            ]
        );
        $resolver->setDefaults(
            [
                'flush' => true,
                'flush_only_object' => false,
            ]
        );
        $options = $resolver->resolve($options);

        return $options;
    }
}
