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
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Factory\AttributeFactory;
use Pim\Component\Catalog\Model\ProductValueInterface;
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
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param RendererInterface                     $renderer
     * @param TranslatorInterface                   $translator
     * @param ProductBuilderInterface               $productBuilder
     * @param AttributeFactory                      $attributeFactory
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

        foreach ($this->presenters as $presenters) {
            foreach ($presenters as $presenter) {
                if ($presenter instanceof TwigAwareInterface) {
                    $presenter->setTwig($this->twig);
                }
            }
        }
    }

    /**
     * Add a presenter
     *
     * @param PresenterInterface $presenter
     * @param int                $priority
     */
    public function addPresenter(PresenterInterface $presenter, $priority)
    {
        if ($presenter instanceof TranslatorAwareInterface) {
            $presenter->setTranslator($this->translator);
        }

        if ($presenter instanceof RendererAwareInterface) {
            $presenter->setRenderer($this->renderer);
        }

        if ($presenter instanceof TwigAwareInterface && null !== $this->twig) {
            $presenter->setTwig($this->twig);
        }

        $this->presenters[$priority][] = $presenter;

        ksort($this->presenters);
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
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'present_new_change',
                [$this, 'presentNewChange'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFilter(
                'present_original_change',
                [$this, 'presentOriginalChange'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_attribute_label_from_code',
                [$this, 'getAttributeLabelFromCode'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getAttributeLabelFromCode($code)
    {
        if (null !== $attribute = $this->attributeRepository->findOneByIdentifier($code)) {
            return (string) $attribute;
        }

        return $code;
    }

    /**
     * @param array  $change
     * @param string $code
     *
     * @return string
     */
    public function presentNewChange(array $change, $code)
    {
        $value = $this->createFakeValue($code);

        foreach ($this->presenters as $presenters) {
            foreach ($presenters as $presenter) {
                if ($presenter->supports($value)) {
                    return $presenter->presentNew($value, $change);
                }
            }
        }

        return '';
    }

    /**
     * @param array  $change
     * @param string $code
     *
     * @return string
     */
    public function presentOriginalChange(ProductValueInterface $value = null, array $change = null)
    {
        if (null === $value) {
            return '';
        }

        foreach ($this->presenters as $presenters) {
            foreach ($presenters as $presenter) {
                if ($presenter->supports($value)) {
                    return $presenter->presentOriginal($value, $change);
                }
            }
        }

        return '';
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
        $attribute    = $this->attributeRepository->findOneByIdentifier($code);
        $newAttribute = $this->attributeFactory->createAttribute($attribute->getAttributeType());
        $value        = $this->productBuilder->createProductValue($newAttribute);

        if (null !== $attribute->getReferenceDataName()) {
            $newAttribute->setReferenceDataName($attribute->getReferenceDataName());
        }

        $value->setAttribute($newAttribute);

        return $value;
    }
}
