<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var PresenterInterface[] */
    protected $presenters = [];

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param ObjectRepository $repository
     * @param TranslatorInterface $translator
     */
    public function __construct(ObjectRepository $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
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
            return $this->present($value->getAttribute());
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
            throw new \InvalidArgumentException('Could not retrieve the product value from the provided change');
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
    public function addPresenter(PresenterInterface $presenter)
    {
        $this->presenters[] = $presenter;
    }

    /**
     * Get the registered presenters
     *
     * @return PresenterInterface[]
     */
    public function getPresenters()
    {
        return $this->presenters;
    }

    /**
     * Wether or not the presenter uses the translator aware trait
     *
     * @param PresenterInterface $presenter
     *
     * @return boolean
     */
    protected function isTranslatorAware(PresenterInterface $presenter)
    {
        return in_array('PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAware', class_uses($presenter));
    }

    /**
     * Present an object
     *
     * @param object $object
     * @param array $change
     *
     * @return null|string
     */
    protected function present($object, array $change = [])
    {
        foreach ($this->presenters as $presenter) {
            if ($presenter->supports($object, $change)) {

                if ($this->isTranslatorAware($presenter)) {
                    $presenter->setTranslator($this->translator);
                }

                return $presenter->present($object, $change);
            }
        }
    }
}
