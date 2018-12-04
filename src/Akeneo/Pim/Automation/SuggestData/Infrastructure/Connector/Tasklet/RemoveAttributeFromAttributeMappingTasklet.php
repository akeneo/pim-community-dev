<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveAttributeFromAttributeMappingTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingHandler;

    /** @var UpdateAttributesMappingByFamilyHandler */
    private $updateAttributesMappingHandler;

    /**
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingHandler
     * @param UpdateAttributesMappingByFamilyHandler $updateAttributesMappingHandler
     */
    public function __construct(
        GetAttributesMappingByFamilyHandler $getAttributesMappingHandler,
        UpdateAttributesMappingByFamilyHandler $updateAttributesMappingHandler
    ) {
        $this->getAttributesMappingHandler = $getAttributesMappingHandler;
        $this->updateAttributesMappingHandler = $updateAttributesMappingHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $pimAttributeCode = $this->stepExecution->getJobParameters()->get('pim_attribute_code');
        $familyCode = $this->stepExecution->getJobParameters()->get('family_code');

        $attributesMapping = $this->getAttributesMappingHandler->handle(
            new GetAttributesMappingByFamilyQuery($familyCode)
        );

        if (!$attributesMapping->hasPimAttribute(new AttributeCode($pimAttributeCode))) {
            return;
        }

        $newMapping = $this->buildNewAttributesMapping($attributesMapping, $pimAttributeCode);

        $command = new UpdateAttributesMappingByFamilyCommand($familyCode, $newMapping);
        $this->updateAttributesMappingHandler->handle($command);
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * @param AttributesMappingResponse $attributesMapping
     * @param string $pimAttributeCode
     *
     * @return array
     */
    private function buildNewAttributesMapping(AttributesMappingResponse $attributesMapping, string $pimAttributeCode)
    {
        $newMapping = [];

        foreach ($attributesMapping as $mapping) {
            $attributeMapping = [
                'franklinAttribute' => [
                    'type' => $mapping->getTargetAttributeType(),
                ],
                'attribute' => $mapping->getPimAttributeCode(),
            ];

            if ($mapping->getPimAttributeCode() === $pimAttributeCode) {
                $attributeMapping['attribute'] = null;
            }

            $newMapping[$mapping->getTargetAttributeCode()] = $attributeMapping;
        }

        return $newMapping;
    }
}
