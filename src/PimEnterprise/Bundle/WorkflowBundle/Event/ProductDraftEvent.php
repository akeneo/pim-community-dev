<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use PimEnterprise\Bundle\WorkflowBundle\Model\Proposition;

/**
 * Proposition event
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductDraftEvent extends Event
{
    /** @var Proposition */
    protected $productDraft;

    /** @var array */
    protected $changes;

    /**
     * @param Proposition $productDraft
     * @param array       $changes
     */
    public function __construct(Proposition $productDraft, array $changes = null)
    {
        $this->productDraft = $productDraft;
        $this->changes = $changes;
    }

    /**
     * Get the proposition
     *
     * @return Proposition
     */
    public function getProductDraft()
    {
        return $this->productDraft;
    }

    /**
     * Get the submitted changes
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Overrides the submitted changes
     *
     * @param array $changes
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;
    }
}
