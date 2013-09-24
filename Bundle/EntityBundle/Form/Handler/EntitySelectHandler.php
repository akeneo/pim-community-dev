<?php

namespace Oro\Bundle\EntityBundle\Form\Handler;

use Oro\Bundle\EntityBundle\Form\Type\EntitySelectType;
use Oro\Bundle\EntityBundle\ORM\OroEntityManager;
use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

class EntitySelectHandler implements SearchHandlerInterface
{
    /**
     * @var OroEntityManager
     */
    protected $entityManager;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param OroEntityManager $entityManager
     * @param Container $container
     */
    public function __construct(OroEntityManager $entityManager, Container $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        //var_dump($item);
        var_dump(get_class($item));

        /*$fieldConfig = $this->entityManager->getExtendManager()->getConfigProvider()->getConfig(
            $form->getParent()->getConfig()->getDataClass(),
            $form->getName()
        );*/

        //$fieldName = $fieldConfig->get('target_field');


    }

    /**
     * {@inheritdoc}
     */
    public function search($query, $page, $perPage)
    {
        //var_dump($query);
    }

    /**
     * {@inheritdoc}
     */
    public function getProperties()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {

    }
}
