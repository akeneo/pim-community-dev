<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;

/**
 * Twig extension to present proposition changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PropositionChangesExtension extends \Twig_Extension
{
    /** @var ObjectRepository */
    protected $valueRepository;

    /** @var ObjectRepository */
    protected $attributeRepository;

    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ProductManager */
    protected $productManager;

    /** @var AttributeManager */
    protected $attributeManager;

    /** @var PresenterInterface[] */
    protected $presenters = [];

    /**
     * @param ObjectRepository    $valueRepository
     * @param RendererInterface   $renderer
     * @param TranslatorInterface $translator
     * @param ProductManager      $productManager
     * @param AttributeManager    $attributeManager
     */
    public function __construct(
        ObjectRepository $valueRepository,
        ObjectRepository $attributeRepository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        ProductManager $productManager,
        AttributeManager $attributeManager
    ) {
        $this->valueRepository = $valueRepository;
        $this->attributeRepository = $attributeRepository;
        $this->renderer = $renderer;
        $this->translator = $translator;
        $this->productManager = $productManager;
        $this->attributeManager = $attributeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_workflow_proposition_changes_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'present_proposition_attribute',
                [$this, 'presentAttribute'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'present_proposition_change',
                [$this, 'presentChange'],
                ['is_safe' => ['html']]
            ),
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
        if (isset($change['__context__']['attribute_id'])
            && null !== $attribute = $this->attributeRepository->find($change['__context__']['attribute_id'])) {
            return $this->present($attribute, $change);
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function presentChange(array $change)
    {
        if (!isset($change['__context__']['value_id'])
            || null === $value = $this->valueRepository->find($change['__context__']['value_id'])) {
            $value = $this->createFakeValue();
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
     * @param int                $priority
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
                if ($presenter instanceof TranslatorAwareInterface) {
                    $presenter->setTranslator($this->translator);
                }

                if ($presenter instanceof RendererAwareInterface) {
                    $presenter->setRenderer($this->renderer);
                }

                return $presenter->present($object, $change);
            }
        }
    }

    /**
     * Create a fake value
     *
     * @return ProductValue
     */
    protected function createFakeValue()
    {
        $value = $this->productManager->createProductValue();
        $attribute = $this->attributeManager->createAttribute('pim_catalog_text');
        $value->setAttribute($attribute);

        return $value;
    }
}
