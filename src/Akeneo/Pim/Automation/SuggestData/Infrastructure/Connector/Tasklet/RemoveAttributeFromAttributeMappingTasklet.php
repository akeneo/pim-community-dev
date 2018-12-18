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
        $pimAttributeCodes = array_map(function (string $attributeCode) {
            return new AttributeCode($attributeCode);
        }, $this->getJobParameterValue('pim_attribute_codes'));
        $familyCode = $this->getJobParameterValue('family_code');

        $attributesMapping = $this->getAttributesMappingHandler->handle(
            new GetAttributesMappingByFamilyQuery($familyCode)
        );

        $hasAtLeastOneAttribute = false;
        foreach ($pimAttributeCodes as $pimAttributeCode) {
            if ($attributesMapping->hasPimAttribute($pimAttributeCode)) {
                $hasAtLeastOneAttribute = true;
                break;
            }
        }
        if (!$hasAtLeastOneAttribute) {
            return;
        }

        $newMapping = $this->buildNewAttributesMapping($attributesMapping, $pimAttributeCodes);

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
     * @param string[] $pimAttributeCodes
     *
     * @return array
     */
    private function buildNewAttributesMapping(AttributesMappingResponse $attributesMapping, array $pimAttributeCodes)
    {
        $newMapping = [];

        foreach ($attributesMapping as $mapping) {
            $attributeMapping = [
                'franklinAttribute' => [
                    'type' => $mapping->getTargetAttributeType(),
                ],
                'attribute' => $mapping->getPimAttributeCode(),
            ];

            if (false !== array_search($mapping->getPimAttributeCode(), $pimAttributeCodes)) {
                $attributeMapping['attribute'] = null;
            }

            $newMapping[$mapping->getTargetAttributeCode()] = $attributeMapping;
        }

        return $newMapping;
    }

    /**
     * @param string $parameterName
     *
     * @return string|array
     */
    private function getJobParameterValue(string $parameterName)
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
