<?php

namespace Pim\Bundle\CustomEntityBundle\Configuration;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CustomEntityBundle\ControllerWorker\WorkerInterface;
use Pim\Bundle\CustomEntityBundle\Manager\ManagerInterface;

/**
 * Configuration for an ORM custom entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @var WorkerInterface
     */
    protected $worker;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param string           $name
     * @param ManagerInterface $manager
     * @param WorkerInterface  $worker
     * @param array            $options
     */
    public function __construct($name, ManagerInterface $manager, WorkerInterface $worker, array $options)
    {
        $this->name = $name;
        $this->manager = $manager;
        $this->worker = $worker;
        $optionsResolver = new OptionsResolver;
        $this->setDefaultOptions($optionsResolver);
        $this->options = $optionsResolver->resolve($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getWorker()
    {
        return $this->worker;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return $this->options['entity_class'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateRoute()
    {
        return $this->options['create_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditRoute()
    {
        return $this->options['edit_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexRoute()
    {
        return $this->options['index_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoveRoute()
    {
        return $this->options['remove_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTemplate()
    {
        return $this->options['base_template'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateFormOptions()
    {
        return (null === $this->options['create_form_options'])
            ? $this->options['edit_form_options']
            : $this->options['create_form_options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateFormType()
    {
        return $this->options['create_form_type']?:$this->options['edit_form_type'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateRedirectRoute($entity)
    {
        return $this->options['edit_after_create']
            ? $this->options['edit_route']
            : $this->options['index_route'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateRedirectRouteParameters($entity)
    {
        $parameters = array('customEntityName' => $this->getName());
        if ($this->options['edit_after_create']) {
            $parameters['id'] = $entity->getId();
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateTemplate()
    {
        return $this->options['create_template'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditFormType()
    {
        return $this->options['edit_form_type'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditFormOptions()
    {
        return $this->options['edit_form_options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditRedirectRoute($entity)
    {
        return $this->options['index_route'] ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditRedirectRouteParameters($entity)
    {
        return array(
            'customEntityName' => $this->getName()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getEditTemplate()
    {
        return $this->options['edit_template'];
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexTemplate()
    {
        return $this->options['index_template'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagridNamespace()
    {
        return $this->options['datagrid_namespace'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateDefaultProperties()
    {
        return $this->options['create_default_properties'];
    }

    /**
     * {@inheritdoc}
     */
    public function getCreateOptions()
    {
        return $this->options['create_options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFindOptions()
    {
        return $this->options['find_options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilderOptions()
    {
        return $this->options['query_builder_options'];
    }

    /**
     * Set the default options
     *
     * @param OptionsResolverInterface $optionsResolver
     */
    protected function setDefaultOptions(OptionsResolverInterface $optionsResolver)
    {
        $optionsResolver->setRequired(
            array(
                'entity_class',
                'edit_form_type',
            )
        );
        $optionsResolver->setDefaults(
            array(
                'base_template'                     => 'PimCustomEntityBundle::layout.html.twig',
                'edit_template'                     => 'PimCustomEntityBundle:CustomEntity:edit.html.twig',
                'index_template'                    => 'PimCustomEntityBundle:CustomEntity:index.html.twig',
                'create_template'                   => 'PimCustomEntityBundle:CustomEntity:quickcreate.html.twig',
                'create_form_options'               => null,
                'create_form_type'                  => null,
                'create_default_properties'         => array(),
                'create_options'                    => array(),
                'index_route'                       => 'pim_customentity_index',
                'create_route'                      => 'pim_customentity_create',
                'edit_route'                        => 'pim_customentity_edit',
                'remove_route'                      => 'pim_customentity_remove',
                'edit_after_create'                 => true,
                'edit_form_options'                 => array(),
                'find_options'                      => array(),
                'query_builder_options'             => array(),
                'datagrid_namespace'                => 'pim_custom_entity'
            )
        );
    }
}
