<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Twig;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Twig extension to present product draft changes
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftChangesExtension extends \Twig_Extension
{
     /** @var IdentifiableObjectRepositoryInterface */
     protected $attributeRepository;

    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var AttributeFactory */
    protected $attributeFactory;

    /** @var PresenterInterface[] */
    protected $presenters = [];

    /** @var \Twig_Environment */
    protected $twig;

    /**
     * @param IdentifiableObjectRepositoryInterface       $attributeRepository
     * @param RendererInterface       $renderer
     * @param TranslatorInterface     $translator
     * @param ProductBuilderInterface $productBuilder
     * @param AttributeFactory        $attributeFactory
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        RendererInterface $renderer,
        TranslatorInterface $translator,
        ProductBuilderInterface $productBuilder,
        AttributeFactory $attributeFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->renderer            = $renderer;
        $this->translator          = $translator;
        $this->productBuilder      = $productBuilder;
        $this->attributeFactory    = $attributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_workflow_product_draft_changes_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'present_product_draft_change',
                [$this, 'presentChange'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Present an attribute change
     *
     * @param ProductDraftInterface $productDraft
     * @param array                 $change
     * @param string                $code
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return string
     */
    public function presentChange(ProductDraftInterface $productDraft, array $change, $code)
    {
        if (null === $value = $productDraft->getProduct()->getValue($code, $change['locale'], $change['scope'])) {
            $value = $this->createFakeValue($code);
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
            if ($presenter->supports($object)) {
                if ($presenter instanceof TranslatorAwareInterface) {
                    $presenter->setTranslator($this->translator);
                }

                if ($presenter instanceof RendererAwareInterface) {
                    $presenter->setRenderer($this->renderer);
                }

                if ($presenter instanceof TwigAwareInterface) {
                    $presenter->setTwig($this->twig);
                }

                return $presenter->present($object, $change);
            }
        }
    }

    /**
     * Create a fake value
     *
     * @param string $code
     *
     * @return ProductValueInterface
     */
    protected function createFakeValue($code)
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);
        $newAttribute = $this->attributeFactory->createAttribute($attribute->getAttributeType());
        $value = $this->productBuilder->createProductValue($newAttribute);

        if (null !== $attribute->getReferenceDataName()) {
            $newAttribute->setReferenceDataName($attribute->getReferenceDataName());
        }

        $value->setAttribute($newAttribute);

        return $value;
    }
}
