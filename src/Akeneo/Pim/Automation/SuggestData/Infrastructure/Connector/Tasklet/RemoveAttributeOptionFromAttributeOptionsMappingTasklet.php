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

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveAttributeOptionFromAttributeOptionsMappingTasklet implements TaskletInterface
{
    /** @var SelectFamilyCodesByAttributeQueryInterface */
    private $familyCodesByAttributeQuery;

    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingHandler;

    /** @var GetAttributeOptionsMappingHandler */
    private $getAttributeOptionsMappingHandler;

    /** @var SaveAttributeOptionsMappingHandler */
    private $saveAttributeOptionsMappingHandler;

    /** @var StepExecution */
    private $stepExecution;

    public function __construct(
        SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery,
        GetAttributesMappingByFamilyHandler $getAttributesMappingHandler,
        GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler,
        SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
    ) {
        $this->familyCodesByAttributeQuery = $familyCodesByAttributeQuery;
        $this->getAttributesMappingHandler = $getAttributesMappingHandler;
        $this->getAttributeOptionsMappingHandler = $getAttributeOptionsMappingHandler;
        $this->saveAttributeOptionsMappingHandler = $saveAttributeOptionsMappingHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        $deletedAttributeOptionCode = $this->stepExecution->getJobParameters()->get('pim_attribute_option_code');
        $pimAttributeCode = $this->stepExecution->getJobParameters()->get('pim_attribute_code');

        $familyCodes = $this->familyCodesByAttributeQuery->execute($pimAttributeCode);

        foreach ($familyCodes as $familyCode) {
            $attributesMapping = $this->getAttributesMappingHandler->handle(
                new GetAttributesMappingByFamilyQuery($familyCode)
            );

            if (!$attributesMapping->hasPimAttribute(new AttributeCode($pimAttributeCode))) {
                return;
            }

            foreach ($attributesMapping as $attributeMapping) {
                $this->updateAttributeOptionsMapping(
                    $attributeMapping,
                    $familyCode,
                    $pimAttributeCode,
                    $deletedAttributeOptionCode
                );
            }
        }
    }

    private function updateAttributeOptionsMapping(
        AttributeMapping $attributeMapping,
        string $familyCode,
        string $pimAttributeCode,
        string $deletedAttributeOptionCode
    ): void {
        $attributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle(
            new GetAttributeOptionsMappingQuery(
                new FamilyCode($familyCode),
                new FranklinAttributeId($attributeMapping->getTargetAttributeCode())
            )
        );

        if (!$attributeOptionsMapping->hasPimAttributeOption($deletedAttributeOptionCode)) {
            return;
        }

        $newMapping = $this->buildNewAttributeOptionsMapping($attributeOptionsMapping, $deletedAttributeOptionCode);

        $command = new SaveAttributeOptionsMappingCommand(
            new FamilyCode($familyCode),
            new AttributeCode($pimAttributeCode),
            new FranklinAttributeId($attributeMapping->getTargetAttributeCode()),
            new AttributeOptions($newMapping)
        );

        $this->saveAttributeOptionsMappingHandler->handle($command);
    }

    private function buildNewAttributeOptionsMapping(
        AttributeOptionsMapping $attributeOptionsMapping,
        string $deletedAttributeOptionCode
    ): array {
        $newMapping = [];

        foreach ($attributeOptionsMapping as $attributeOptionMapping) {
            $mapping = [
                'franklinAttributeOptionCode' => [
                    'label' => $attributeOptionMapping->franklinAttributeLabel(),
                ],
                'catalogAttributeOptionCode' => $attributeOptionMapping->catalogAttributeCode(),
                'status' => $attributeOptionMapping->status(),
            ];

            if ($deletedAttributeOptionCode === $attributeOptionMapping->catalogAttributeCode()) {
                $mapping['catalogAttributeOptionCode'] = null;
            }

            $newMapping[$attributeOptionMapping->franklinAttributeId()] = $mapping;
        }

        return $newMapping;
    }
}
