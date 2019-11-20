<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job;

use Akeneo\Pim\Structure\Component\AttributeGroup\Query\FindAttributeGroupOrdersEqualOrSuperiorTo;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * For each attribute group imported by file, we will check if its sort order
 * is in conflict with an existing attribute group.
 *
 * In case of conflict, a new available sort order will be given to it.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EnsureConsistentAttributeGroupOrderTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeGroupRepository;

    /** @var ItemReaderInterface */
    private $attributeGroupReader;

    /** @var SaverInterface */
    private $attributeGroupSaver;

    /** @var FindAttributeGroupOrdersEqualOrSuperiorTo */
    private $findAttributeGroupOrdersEqualOrSuperiorTo;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        IdentifiableObjectRepositoryInterface $attributeGroupRepository,
        ItemReaderInterface $attributeGroupReader,
        SaverInterface $attributeGroupSaver,
        FindAttributeGroupOrdersEqualOrSuperiorTo $findAttributeGroupOrdersEqualOrSuperiorTo,
        ValidatorInterface $validator
    ) {
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupReader = $attributeGroupReader;
        $this->attributeGroupSaver = $attributeGroupSaver;
        $this->findAttributeGroupOrdersEqualOrSuperiorTo = $findAttributeGroupOrdersEqualOrSuperiorTo;
        $this->validator = $validator;
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
    public function execute()
    {
        while (true) {
            try {
                $attributeGroupItem = $this->attributeGroupReader->read();

                if (null === $attributeGroupItem) {
                    break;
                }
            } catch (InvalidItemException $e) {
                continue;
            }

            /** @var AttributeGroupInterface $attributeGroup */
            $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupItem['code']);

            if (null === $attributeGroup) {
                $this->stepExecution->incrementSummaryInfo('skip');

                continue;
            }

            $ordersEqualsOrSuperior = $this->findAttributeGroupOrdersEqualOrSuperiorTo->execute($attributeGroup);

            // If there is a conflict in sort order, set the next one available
            if (!empty($ordersEqualsOrSuperior) && (int) current($ordersEqualsOrSuperior) === (int) $attributeGroup->getSortOrder()) {
                $rangeOrders = range(min($ordersEqualsOrSuperior), max($ordersEqualsOrSuperior));
                $availableOrders = array_diff($rangeOrders, $ordersEqualsOrSuperior);

                if (!empty($availableOrders)) {
                    $nextAvailableOrder = current($availableOrders);
                } else {
                    $nextAvailableOrder = max($ordersEqualsOrSuperior) + 1;
                }

                $attributeGroup->setSortOrder($nextAvailableOrder);
                $violations = $this->validator->validate($attributeGroup);

                if ($violations->count() > 0) {
                    $this->stepExecution->incrementSummaryInfo('skip');

                    continue;
                }

                $this->attributeGroupSaver->save($attributeGroup);
                $this->stepExecution->incrementSummaryInfo('process');
            } else {
                $this->stepExecution->incrementSummaryInfo('skip');
            }
        }
    }
}
