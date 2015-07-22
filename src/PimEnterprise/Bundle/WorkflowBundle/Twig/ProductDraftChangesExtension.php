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

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Pim\Bundle\CatalogBundle\Manager\AttributeManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\PresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\RendererAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Rendering\RendererInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;

/**
 * Twig extension to present product draft changes
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraftChangesExtension extends \Twig_Extension
{
    /** @var ObjectRepository */
    protected $valueRepository;

    /** @var ReferableEntityRepositoryInterface */
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

    /** @var \Twig_Environment */
    protected $twig;

    /**
     * @param ObjectRepository                   $valueRepository
     * @param ReferableEntityRepositoryInterface $attributeRepository
     * @param RendererInterface                  $renderer
     * @param TranslatorInterface                $translator
     * @param ProductManager                     $productManager
     * @param AttributeManager                   $attributeManager
     */
    public function __construct(
        ObjectRepository $valueRepository,
        ReferableEntityRepositoryInterface $attributeRepository,
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
                'present_product_draft_attribute',
                [$this, 'presentAttribute'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'present_product_draft_change',
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
        if (isset($change['__context__']['attribute'])
            && null !== $attribute = $this->attributeRepository->findByReference($change['__context__']['attribute'])) {
            return $this->present($attribute, $change);
        }

        return $default;
    }

    /**
     * Present an attribute change
     *
     * @param array        $change
     * @param ProductDraft $productDraft
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     *
     * @return string
     */
    public function presentChange(array $change, ProductDraft $productDraft)
    {
        $change['__context__'] = array_merge(
            [
                'attribute' => null,
                'locale'    => null,
                'scope'     => null,
            ],
            $change['__context__']
        );

        $attribute = $change['__context__']['attribute'];
        $locale = $change['__context__']['locale'];
        $scope = $change['__context__']['scope'];

        if (null === $value = $productDraft->getProduct()->getValue($attribute, $locale, $scope)) {
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
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface
     */
    protected function createFakeValue()
    {
        $value = $this->productManager->createProductValue();
        $attribute = $this->attributeManager->createAttribute('pim_catalog_text');
        $value->setAttribute($attribute);

        return $value;
    }
}
