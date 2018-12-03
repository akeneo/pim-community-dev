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
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeOption\ValueObject\AttributeOptions;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\Query\SelectFamilyCodesByAttributeQueryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\FranklinAttributeId;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * There are 2 inputs parameters on this.
 *
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
            $this->updateOptionsMappingByFamily($familyCode, $pimAttributeCode, $deletedAttributeOptionCode);
        }
    }

    /**
     * @param string $familyCode
     * @param string $pimAttributeCode
     * @param string $deletedAttributeOptionCode
     */
    private function updateOptionsMappingByFamily(
        string $familyCode,
        string $pimAttributeCode,
        string $deletedAttributeOptionCode
    ): void {
        $familyAttributesMapping = $this->getAttributesMappingHandler->handle(
            new GetAttributesMappingByFamilyQuery($familyCode)
        );

        if (!$familyAttributesMapping->hasPimAttribute(new AttributeCode($pimAttributeCode))) {
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

    /**
     * @param string $franklinAttributeCode
     * @param string $familyCode
     * @param string $pimAttributeCode
     * @param string $deletedAttributeOptionCode
     */
    private function updateAttributeOptionsMapping(
        string $franklinAttributeCode,
        string $familyCode,
        string $pimAttributeCode,
        string $deletedAttributeOptionCode
    ): void {
        $attributeOptionsMapping = $this->getAttributeOptionsMappingHandler->handle(
            new GetAttributeOptionsMappingQuery(
                new FamilyCode($familyCode),
                new FranklinAttributeId($franklinAttributeCode)
            )
        );

        if (!$attributeOptionsMapping->hasPimAttributeOption($deletedAttributeOptionCode)) {
            return;
        }

        $newMapping = $this->buildNewAttributeOptionsMapping($attributeOptionsMapping, $deletedAttributeOptionCode);

        $command = new SaveAttributeOptionsMappingCommand(
            new FamilyCode($familyCode),
            new AttributeCode($pimAttributeCode),
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
        string $deletedAttributeOptionCode
    ): array {
        $newAttributeOptionsMapping = [];

        foreach ($attributeOptionsMapping->mapping() as $currentOptionMapping) {
            $newOptionMapping = $this->buildNewOptionMapping($deletedAttributeOptionCode, $currentOptionMapping);
            $newAttributeOptionsMapping[$currentOptionMapping->franklinAttributeId()] = $newOptionMapping;
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
        string $deletedAttributeOptionCode,
        AttributeOptionMapping $currentOptionMapping
    ): array {
        $newMappedOptionCode = $currentOptionMapping->catalogAttributeCode() === $deletedAttributeOptionCode ?
            null : $currentOptionMapping->catalogAttributeCode();

        $newOptionMapping = [
            'franklinAttributeOptionCode' => [
                'label' => $currentOptionMapping->franklinAttributeLabel(),
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
