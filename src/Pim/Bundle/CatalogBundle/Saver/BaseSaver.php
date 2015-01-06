<?php

namespace Pim\Bundle\CatalogBundle\Saver;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Component\Persistence\SaverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base saver, declared as different services for different classes
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSaver implements SaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var BaseSavingOptionsResolver */
    protected $optionsResolver;

    /** @var string */
    protected $savedClass;

    /**
     * @param ObjectManager             $objectManager
     * @param BaseSavingOptionsResolver $optionsResolver
     * @param string                    $savedClass
     */
    public function __construct(
        ObjectManager $objectManager,
        BaseSavingOptionsResolver $optionsResolver,
        $savedClass
    ) {
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
        $this->savedClass = $savedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        if (!$object instanceof $this->savedClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->savedClass,
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($object);

        if (true === $options['flush'] && true === $options['flush_only_object']) {
            $this->objectManager->flush($object);
        } elseif (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }
}
