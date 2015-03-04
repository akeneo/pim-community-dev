<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\Common\Remover;

use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\Remover\RemovingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;

/**
 * Base remover, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseRemover implements RemoverInterface, BulkRemoverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var RemovingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var string */
    protected $removedClass;

    /**
     * @param ObjectManager                    $objectManager
     * @param RemovingOptionsResolverInterface $optionsResolver
     * @param string                           $removedClass
     */
    public function __construct(
        ObjectManager $objectManager,
        RemovingOptionsResolverInterface $optionsResolver,
        $removedClass
    ) {
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->removedClass = $removedClass;
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

        $options = $this->optionsResolver->resolveRemoveOptions($options);
        $this->objectManager->remove($object);

        if (true === $options['flush'] && true === $options['flush_only_object']) {
            $this->objectManager->flush($object);
        } elseif (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeAll(array $objects, array $options = [])
    {
        if (empty($objects)) {
            return;
        }

        $allOptions = $this->optionsResolver->resolveRemoveAllOptions($options);
        $itemOptions = $allOptions;
        $itemOptions['flush'] = false;

        foreach ($objects as $object) {
            $this->remove($object, $itemOptions);
        }

        if (true === $allOptions['flush']) {
            $this->objectManager->flush();
        }
    }
}
