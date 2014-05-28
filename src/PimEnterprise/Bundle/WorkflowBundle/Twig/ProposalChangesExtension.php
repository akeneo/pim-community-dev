<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Twig
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalChangesExtension extends \Twig_Extension
{
    /** @var ObjectRepository */
    protected $repository;

    /** @var PresenterInterface[] */
    protected $presenters = [];

    protected $renderer;

    #public function __construct(\Diff_Renderer_Html_Array $renderer)
    #{
    #    $this->renderer = $renderer;
    #}

    public function __construct(ObjectRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('present_proposal_attribute', [$this, 'presentAttribute'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('present_proposal_change', [$this, 'presentChange'], ['is_safe' => ['html']]),
        ];
    }

    public function presentAttribute(array $change, $default)
    {
        if (isset($change['id']) && null !== $value = $this->repository->find($change['id'])) {
            return (string) $value->getAttribute();
        }

        return $default;
    }

    public function presentChange(array $change)
    {
        foreach ($this->presenters as $presenter) {
            if ($presenter->supportsChange($change)) {
                if (isset($change['id']) && null !== $value = $this->repository->find($change['id'])) {
                    return $presenter->present($value->getData(), $change);
                }
            }
        }

        throw new \LogicException('No presenter supports the provided change');
    }

    public function getName()
    {
        return 'pimee_workflow_proposal_changes_extension';
    }


    public function addPresenter(PresenterInterface $presenter)
    {
        $this->presenters[] = $presenter;
    }

    public function getPresenters()
    {
        return $this->presenters;
    }
}
