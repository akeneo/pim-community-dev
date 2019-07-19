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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Tasklet;

use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Command\SaveAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributeOptionsMappingQuery;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOptionMapping\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOptionMapping\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOptionMapping\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeOptionCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class RemoveOptionFromAttributeOptionsMappingTasklet implements TaskletInterface
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

    /**
     * @param SelectFamilyCodesByAttributeQueryInterface $familyCodesByAttributeQuery
     * @param GetAttributesMappingByFamilyHandler $getAttributesMappingHandler
     * @param GetAttributeOptionsMappingHandler $getAttributeOptionsMappingHandler
     * @param SaveAttributeOptionsMappingHandler $saveAttributeOptionsMappingHandler
     */
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
        $deletedAttributeOptionCode = $this->getJobParameterValue('pim_attribute_option_code');
        $pimAttributeCode = $this->getJobParameterValue('pim_attribute_code');

        $familyCodes = $this->familyCodesByAttributeQuery->execute($pimAttributeCode);

        foreach ($familyCodes as $familyCode) {
            $this->updateOptionsMappingByFamily(
                new FamilyCode($familyCode),
                new AttributeCode($pimAttributeCode),
                new AttributeOptionCode($deletedAttributeOptionCode)
            );
        }
    }

    private function updateOptionsMappingByFamily(
        FamilyCode $familyCode,
        AttributeCode $pimAttributeCode,
        AttributeOptionCode $deletedAttributeOptionCode
    ): void {
        $familyAttributesMapping = $this->getAttributesMappingHandler->handle(
            new GetAttributesMappingByFamilyQuery($familyCode)
        );

        if (!$familyAttributesMapping->hasPimAttribute($pimAttributeCode)) {
            return;
        }

        foreach ($familyAttributesMapping as $attributeMapping) {
            $this->updateAttributeOptionsMapping(
                $attributeMapping->getTargetAttributeCode(),
                $familyCode,
                $pimAttributeCode,
                $deletedAttributeOptionCode
            );
        }
    }

    private function updateAttributeOptionsMapping(
        string $franklinAttributeCode,
        FamilyCode $familyCode,
        AttributeCode $pimAttributeCode,
        AttributeOptionCode $deletedAttributeOptionCode
    ): void {
        $attributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle(
            new GetAttributeOptionsMappingQuery(
                $familyCode,
                new FranklinAttributeId($franklinAttributeCode)
            )
        );

        if (!$attributeOptionsMapping->hasPimAttributeOption($deletedAttributeOptionCode)) {
            return;
        }

        $newMapping = $this->buildNewAttributeOptionsMapping($attributeOptionsMapping, $deletedAttributeOptionCode);

        $command = new SaveAttributeOptionsMappingCommand(
            $familyCode,
            $pimAttributeCode,
            new FranklinAttributeId($franklinAttributeCode),
            new AttributeOptions($newMapping)
        );

        $this->saveAttributeOptionsMappingHandler->handle($command);
    }

    /**
     * @param AttributeOptionsMapping $attributeOptionsMapping
     * @param string $deletedAttributeOptionCode
     *
     * @return array
     */
    private function buildNewAttributeOptionsMapping(
        AttributeOptionsMapping $attributeOptionsMapping,
        AttributeOptionCode $deletedAttributeOptionCode
    ): array {
        $newAttributeOptionsMapping = [];

        foreach ($attributeOptionsMapping->mapping() as $currentOptionMapping) {
            $newOptionMapping = $this->buildNewOptionMapping($deletedAttributeOptionCode, $currentOptionMapping);
            $newAttributeOptionsMapping[$currentOptionMapping->franklinAttributeOptionId()] = $newOptionMapping;
        }

        return $newAttributeOptionsMapping;
    }

    /**
     * Removes deleted attribute option from the option mapping.
     *
     * @param string $deletedAttributeOptionCode
     * @param AttributeOptionMapping $currentOptionMapping
     *
     * @return array
     */
    private function buildNewOptionMapping(
        AttributeOptionCode $deletedAttributeOptionCode,
        AttributeOptionMapping $currentOptionMapping
    ): array {
        $currentOption = $currentOptionMapping->catalogAttributeOptionCode();
        $newMappedOptionCode =  $currentOption->equals($deletedAttributeOptionCode) ?
            null : (string) $currentOptionMapping->catalogAttributeOptionCode();

        $newOptionMapping = [
            'franklinAttributeOptionCode' => [
                'label' => $currentOptionMapping->franklinAttributeOptionLabel(),
            ],
            'catalogAttributeOptionCode' => $newMappedOptionCode,
            'status' => $currentOptionMapping->status(),
        ];

        return $newOptionMapping;
    }

    /**
     * @param string $parameterName
     *
     * @return string
     */
    private function getJobParameterValue(string $parameterName): string
    {
        if (null === $this->stepExecution->getJobParameters()) {
            throw new \InvalidArgumentException(sprintf(
                'Missing job parameters for tasklet "%s"',
                self::class
            ));
        }
        if (!$this->stepExecution->getJobParameters()->has($parameterName)) {
            throw new \InvalidArgumentException(sprintf(
                'The job parameter "%s" is missing for the tasklet "%s"',
                $parameterName,
                self::class
            ));
        }

        return $this->stepExecution->getJobParameters()->get($parameterName);
    }
}
