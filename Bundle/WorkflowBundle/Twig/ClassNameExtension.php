<?php

namespace Oro\Bundle\WorkflowBundle\Twig;

use Oro\Bundle\WorkflowBundle\Model\DoctrineHelper;

class ClassNameExtension extends \Twig_Extension
{
    const NAME = 'oro_class_name';

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('oro_class_name', array($this, 'getClassName')),
        );
    }

    /**
     * Get FQCN of specified entity
     *
     * @param object $object
     * @return string
     */
    public function getClassName($object)
    {
        if (!is_object($object)) {
            return null;
        }

        return $this->doctrineHelper->getEntityClass($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
