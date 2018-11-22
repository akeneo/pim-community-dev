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
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class RemoveAttributeFromAttributeMappingTasklet implements TaskletInterface
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var GetAttributesMappingByFamilyHandler */
    private $getAttributesMappingHandler;

    /** @var UpdateAttributesMappingByFamilyHandler */
    private $updateAttributesMappingHandler;

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
        $pimAttributeCode = $this->getJobParameterValue('pim_attribute_code');
        $familyCode = $this->getJobParameterValue('family_code');

        $response = $this->getAttributesMappingHandler->handle(new GetAttributesMappingByFamilyQuery($familyCode));

        if (!$response->hasPimAttribute(new AttributeCode($pimAttributeCode))) {
            return;
        }

        $mapping = $this->buildNewAttributeMapping($response, $pimAttributeCode);

        $command = new UpdateAttributesMappingByFamilyCommand($familyCode, $mapping);
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
     * @param AttributesMappingResponse $response
     * @param string $pimAttributeCode
     *
     * @return array
     */
    private function buildNewAttributeMapping(AttributesMappingResponse $response, string $pimAttributeCode)
    {
        $newMapping = [];

        foreach ($response as $mapping) {
            $attributeMapping = [
                'franklinAttribute' => [
                    'type' => $mapping->getTargetAttributeType(),
                ],
                'attribute' => $mapping->getPimAttributeCode(),
            ];

            if ($mapping->getPimAttributeCode() === $pimAttributeCode) {
                $attributeMapping['attribute'] = null;
                $attributeMapping['status'] = AttributeMapping::ATTRIBUTE_PENDING;
            }

            $newMapping[$mapping->getTargetAttributeCode()] = $attributeMapping;
        }

        return $newMapping;
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
                'The job parameter "%" is missing for the tasklet "%s"',
                $parameterName,
                self::class
            ));
        }

        return $this->stepExecution->getJobParameters()->get($parameterName);
    }
}
