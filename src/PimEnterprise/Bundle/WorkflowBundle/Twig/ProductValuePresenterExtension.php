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

use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\ProductValue\ProductValuePresenterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TranslatorAwareInterface;
use PimEnterprise\Bundle\WorkflowBundle\Presenter\TwigAwareInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Twig extension to present a product value
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ProductValuePresenterExtension extends \Twig_Extension
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var ProductValuePresenterInterface[] */
    protected $presenters = [];

    /** @var \Twig_Environment */
    protected $twig;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        return 'pimee_workflow_product_value_presenter_extension';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'present_product_value',
                [$this, 'presentValue'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Present a product value
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function presentValue(ProductValueInterface $value)
    {
        $result = $this->present($value);

        if (null === $result) {
            $result = (string) $value;
        }

        return $result;
    }

    /**
     * Add a presenter
     *
     * @param ProductValuePresenterInterface $presenter
     * @param int                            $priority
     */
    public function addPresenter(ProductValuePresenterInterface $presenter, $priority)
    {
        $this->presenters[$priority][] = $presenter;
    }

    /**
     * Get the registered presenters
     *
     * @return ProductValuePresenterInterface[]
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
     * @param ProductValueInterface $value
     *
     * @return null|string
     */
    protected function present(ProductValueInterface $value)
    {
        foreach ($this->getPresenters() as $presenter) {
            if ($presenter->supports($value)) {
                if ($presenter instanceof TranslatorAwareInterface) {
                    $presenter->setTranslator($this->translator);
                }

                if ($presenter instanceof TwigAwareInterface) {
                    $presenter->setTwig($this->twig);
                }

                return $presenter->present($value);
            }
        }
    }
}
