<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Twig
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class OptionExtension extends \Twig_Extension
{
    protected $repository;

    public function __construct(AttributeOptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('presentOption', [$this, 'presentOption']),
        ];
    }

    /**
     * Present an option
     *
     * @param integer $id
     *
     * @return string
     */
    public function presentOption($id)
    {
        return (string) $this->repository->find($id);
    }

    public function getName()
    {
        return 'pimee_workflow_option_extension';
    }
}
