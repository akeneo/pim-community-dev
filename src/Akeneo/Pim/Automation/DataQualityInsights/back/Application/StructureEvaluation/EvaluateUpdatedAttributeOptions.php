<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\StructureEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeOptionLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributeOptionCodesToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeOptionSpellcheckRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeOptionCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluateUpdatedAttributeOptions
{
    /** @var AttributeOptionSpellcheckRepositoryInterface */
    private $attributeOptionSpellcheckRepository;

    /** @var EvaluateAttributeOptionLabelsSpelling */
    private $evaluateAttributeOptionLabelsSpelling;

    /** @var GetAttributeOptionCodesToEvaluateQueryInterface */
    private $getAttributeOptionCodesToEvaluateQuery;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        AttributeOptionSpellcheckRepositoryInterface $attributeOptionSpellcheckRepository,
        EvaluateAttributeOptionLabelsSpelling $evaluateAttributeOptionLabelsSpelling,
        GetAttributeOptionCodesToEvaluateQueryInterface $getAttributeOptionCodesToEvaluateQuery,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->attributeOptionSpellcheckRepository = $attributeOptionSpellcheckRepository;
        $this->evaluateAttributeOptionLabelsSpelling = $evaluateAttributeOptionLabelsSpelling;
        $this->getAttributeOptionCodesToEvaluateQuery = $getAttributeOptionCodesToEvaluateQuery;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function evaluateSince(\DateTimeImmutable $updatedSince): void
    {
        $attributeOptionsToEvaluate = $this->getAttributeOptionCodesToEvaluateQuery->execute($updatedSince);

        foreach ($attributeOptionsToEvaluate as $attributeOptionCode) {
            $this->evaluate($attributeOptionCode);
        }

        $this->attributeOptionSpellcheckRepository->deleteUnknownAttributeOptions();
    }

    public function evaluate(AttributeOptionCode $attributeOptionCode): void
    {
        $attributeOptionSpellcheck = $this->evaluateAttributeOptionLabelsSpelling->evaluate($attributeOptionCode);
        $this->attributeOptionSpellcheckRepository->save($attributeOptionSpellcheck);
        $this->eventDispatcher->dispatch(new AttributeOptionLabelsSpellingEvaluatedEvent($attributeOptionSpellcheck));
    }
}
