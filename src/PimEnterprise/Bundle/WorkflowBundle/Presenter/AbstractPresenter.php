<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Presenter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPresenter implements PresenterInterface
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
}
