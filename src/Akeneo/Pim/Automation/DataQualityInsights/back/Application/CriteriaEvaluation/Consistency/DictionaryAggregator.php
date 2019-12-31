<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;

final class DictionaryAggregator
{
    public function aggregate(Dictionary ...$dictionaries): Dictionary
    {
        $aggregatedDictionary = new \AppendIterator();

        foreach ($dictionaries as $dictionary) {
            $aggregatedDictionary->append($dictionary->getIterator());
        }

        return new Dictionary(iterator_to_array($aggregatedDictionary));
    }
}
