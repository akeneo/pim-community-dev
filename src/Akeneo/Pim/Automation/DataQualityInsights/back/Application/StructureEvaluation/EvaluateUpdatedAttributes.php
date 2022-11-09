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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Events\Structure\AttributeLabelsSpellingEvaluatedEvent;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAttributesCodesToEvaluateQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\AttributeSpellcheckRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluateUpdatedAttributes
{
    /** @var EvaluateAttributeLabelsSpelling */
    private $evaluateAttributeLabelsSpelling;

    /** @var AttributeSpellcheckRepositoryInterface */
    private $attributeSpellcheckRepository;

    /** @var GetAttributesCodesToEvaluateQueryInterface */
    private $getAttributesToEvaluateQuery;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EvaluateAttributeLabelsSpelling $evaluateAttributeLabelsSpelling,
        AttributeSpellcheckRepositoryInterface $attributeSpellcheckRepository,
        GetAttributesCodesToEvaluateQueryInterface $getAttributesToEvaluateQuery,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->evaluateAttributeLabelsSpelling = $evaluateAttributeLabelsSpelling;
        $this->attributeSpellcheckRepository = $attributeSpellcheckRepository;
        $this->getAttributesToEvaluateQuery = $getAttributesToEvaluateQuery;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function evaluateAll(): void
    {
        $attributesToEvaluate = $this->getAttributesToEvaluateQuery->execute();

        foreach ($attributesToEvaluate as $attributeCode) {
            $this->evaluate($attributeCode);
        }

        $attributesToReevaluate = $this->getAttributesToEvaluateQuery->toReevaluate();

        foreach ($attributesToReevaluate as $attributeCode) {
            if (!in_array($attributeCode, (array) $attributesToEvaluate)) {
                $this->evaluate($attributeCode);
            }
        }

        $this->attributeSpellcheckRepository->deleteUnknownAttributes();
    }

    public function evaluate(AttributeCode $attributeCode): void
    {
        $attributeSpellcheck = $this->evaluateAttributeLabelsSpelling->evaluate($attributeCode);
        $this->attributeSpellcheckRepository->save($attributeSpellcheck);
        $this->eventDispatcher->dispatch(new AttributeLabelsSpellingEvaluatedEvent($attributeSpellcheck));
    }
}
