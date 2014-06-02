<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;

/**
 * Twig extension to present proposal changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProposalChangesExtension extends \Twig_Extension
{
    /** @var ObjectRepository */
    protected $repository;

    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var PresenterInterface[] */
    protected $presenters = [];

    /**
     * @param ObjectRepository    $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ObjectRepository $repository,
        RendererInterface $renderer,
        TranslatorInterface $translator
    ) {
        $this->repository = $repository;
        $this->renderer = $renderer;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_workflow_proposal_changes_extension';
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

    /**
     * Present an attribute (showing its label, scope and localizability)
     *
     * @param array  $change
     * @param string $default
     *
     * @return string
     */
    public function presentAttribute(array $change, $default)
    {
        if (isset($change['id']) && null !== $value = $this->repository->find($change['id'])) {
            return $this->present($value->getAttribute(), ['scope' => $value->getScope()]);
        }

        return $default;
    }

    /**
     * Present an attribute change
     *
     * @param array $change
     *
     * @return string
     *
     * @throws \LogicException
     */
    public function presentChange(array $change)
    {
        if (!isset($change['id']) || null === $value = $this->repository->find($change['id'])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not retrieve the product value from the provided change (missing key "id" in "%s")',
                    join(', ', array_keys($change))
                )
            );
        }

        if (null !== $result = $this->present($value, $change)) {
            return $result;
        }

        throw new \LogicException(
            sprintf(
                'No presenter supports the provided change with key(s) "%s"',
                implode(', ', array_keys($change))
            )
        );
    }

    /**
     * Add a presenter
     *
     * @param PresenterInterface $presenter
     */
    public function addPresenter(PresenterInterface $presenter, $priority)
    {
        $this->presenters[$priority][] = $presenter;
    }

    /**
     * Get the registered presenters
     *
     * @return PresenterInterface[]
     */
    public function getPresenters()
    {
        krsort($this->presenters);

        $presenters = [];
        foreach ($this->presenters as $groupedPresenters) {
            $presenters = array_merge($presenters, $groupedPresenters);
        }

        return $presenters;
    }

    /**
     * Present an object
     *
     * @param object $object
     * @param array  $change
     *
     * @return null|string
     */
    protected function present($object, array $change = [])
    {
        foreach ($this->getPresenters() as $presenter) {
            if ($presenter->supports($object, $change)) {
                $reflClass = new \ReflectionClass($presenter);

                if ($this->usesTrait($reflClass, 'PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAware')) {
                    $presenter->setTranslator($this->translator);
                }

                if ($this->usesTrait($reflClass, 'PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAware')) {
                    $presenter->setRenderer($this->renderer);
                }

                return $presenter->present($object, $change);
            }
        }
    }

    /**
     * Wether or not the class uses the trait
     *
     * @param PresenterInterface $presenter
     * @param string             $traitName
     *
     * @return boolean
     */
    protected function usesTrait(\ReflectionClass $class, $traitName)
    {
        if (in_array($traitName, $class->getTraitNames())) {
            return true;
        }

        $parentClass = $class->getParentClass();

        if (false === $parentClass) {
            return false;
        }

        return $this->usesTrait($parentClass, $traitName);
    }
}
