<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Rendering;

/**
 * Diff renderer based on the PHP-Diff library
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class PhpDiffRenderer implements RendererInterface
{
    /** @var \Diff_Renderer_Html_Array */
    protected $renderer;

    /** @var DiffFactory */
    protected $factory;

    /**
     * @param \Diff_Renderer_Html_Array $renderer
     * @param DiffFactory               $factory
     */
    public function __construct(\Diff_Renderer_Html_Array $renderer, DiffFactory $factory)
    {
        $this->renderer = $renderer;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function renderDiff($before, $after)
    {
        /*
         * Workaround: we add "@" before the call to render to avoid warning in dev environment because of
         * the methods Diff_SequenceMatcher::setSeq1 and Diff_SequenceMatcher::setSeq2.
         * Both methods don't use the strict comparison (===), so an empty array equals to null.
         */
        return @$this->factory->create($before, $after)->render($this->renderer);
    }
}
