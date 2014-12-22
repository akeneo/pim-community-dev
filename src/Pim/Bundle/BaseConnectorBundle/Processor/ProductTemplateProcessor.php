<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;

/**
 * Product template import processor
 *
 * Allows to bind values data into a product template and validate them
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var GroupRepository */
    protected $groupRepository;

    /**
     * @param GroupRepository $groupRepository
     */
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $variantGroup = $this->getVariantGroup($item);

        // TODO, processor
        // - fetch variant group and template
        // - de-normalize values array to values object
        // - validate template and values
        // - detach the element if not valid !
        // - set values (data and objects) to template
        // - return template | variant group ? TODO rename processor

        // TODO, template | variant group writer
        // - save template | variant group (+ cascade)

        // TODO, product updater (from template) or in writer ?
        // - update products
        // - validate
        // - flush

        return $variantGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @param array $item
     *
     * @return GroupInterface
     *
     * @throws InvalidItemException
     */
    protected function getVariantGroup($item)
    {
        if (!isset($item['variant_group_code'])) {
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException("Variant group code must be provided", $item);
        }

        $variantGroup = $this->groupRepository->findOneByCode($item['variant_group_code']);
        if (!$variantGroup || !$variantGroup->getType()->isVariant()) {
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException("Variant group doesn't exist", $item);
        }

        return $variantGroup;
    }
}
