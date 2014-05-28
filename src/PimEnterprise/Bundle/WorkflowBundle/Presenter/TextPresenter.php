<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

/**
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class TextPresenter implements PresenterInterface
{
    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var DiffFactory */
    protected $factory;

    /**
     * @param \Diff_Renderer_Html_Array $renderer
     * @param DiffFactory               $factory
     */
    public function __construct(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory = null)
    {
        $this->renderer = $renderer;
        $this->factory = $factory ?: new DiffFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsChange(array $change)
    {
        return array_key_exists('text', $change);
    }

    /**
     * {@inheritdoc}
     */
    public function present($data, array $change)
    {
        return $this
            ->factory
            ->create(
                $this->explodeText($data),
                $this->explodeText($change['text'])
            )
            ->render($this->renderer);
    }

    protected function explodeText($text)
    {
        preg_match_all('/<p>(.*?)<\/p>/', $text, $matches);

        return !empty($matches[0]) ? $matches[0] : [$text];
    }
}
