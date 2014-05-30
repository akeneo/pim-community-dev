<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Presenter;

use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Diff\Factory\DiffFactory;

/**
 * PimEnterprise\Bundle\WorkflowBundle\Presenter
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductValuePresenter implements PresenterInterface
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
    public function supports($object, array $change)
    {
        return $object instanceof AbstractProductValue && $this->supportsChange($change);
    }

    /**
     * {@inheritdoc}
     */
    public function present($value, array $change)
    {
        return $this
            ->factory
            ->create(
                $this->normalizeData($value->getData()),
                $this->normalizeChange($change)
            )
            ->render($this->renderer);
    }

    /**
     * Wether or not this class can present the provided change
     *
     * @param array $change
     *
     * @return boolean
     */
    abstract protected function supportsChange(array $change);

    /**
     * Normalize data
     *
     * @param mixed $data
     *
     * @return array|string
     */
    protected function normalizeData($data)
    {
        return [];
    }

    /**
     * Normalize change
     *
     * @param array $change
     *
     * @return array|string
     */
    protected function normalizeChange(array $change)
    {
        return [];
    }
}
