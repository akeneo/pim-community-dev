<?php

namespace Oro\Bundle\WorkflowBundle\Twig;

use Oro\Bundle\WorkflowBundle\Model\MetadataManager;

class ClassNameExtension extends \Twig_Extension
{
    const NAME = 'oro_class_name';

    /**
     * @var MetadataManager
     */
    protected $metadataManager;

    /**
     * @param MetadataManager $metadataManager
     */
    public function __construct(MetadataManager $metadataManager)
    {
        $this->metadataManager = $metadataManager;
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

        return $this->metadataManager->getEntityClass($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
