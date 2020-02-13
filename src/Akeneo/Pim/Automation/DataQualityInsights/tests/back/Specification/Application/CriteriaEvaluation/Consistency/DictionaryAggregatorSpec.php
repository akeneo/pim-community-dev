<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use PhpSpec\ObjectBehavior;

final class DictionaryAggregatorSpec extends ObjectBehavior
{
    public function it_aggregate_multiple_empty_dictionaries()
    {
        $dictionaryA = new Dictionary();
        $dictionaryB = new Dictionary();

        $this->aggregate($dictionaryA, $dictionaryB)->shouldBeLike(new Dictionary());
    }

    public function it_aggregate_multiple_dictionaries_even_empty_ones()
    {
        $dictionaryA = new Dictionary(['word' => 'word']);
        $dictionaryB = new Dictionary();

        $this->aggregate($dictionaryA, $dictionaryB)->shouldBeLike(new Dictionary(['word' => 'word']));
    }

    public function it_aggregate_multiple_dictionaries()
    {
        $dictionaryA = new Dictionary(['word' => 'word']);
        $dictionaryB = new Dictionary(['burger' => 'burger']);

        $this->aggregate($dictionaryA, $dictionaryB)->shouldBeLike(new Dictionary(['word' => 'word', 'burger' => 'burger']));
    }

    public function it_deduplicate_multiple_dictionaries()
    {
        $dictionaryA = new Dictionary(['word' => 'word']);
        $dictionaryB = new Dictionary(['word' => 'word']);

        $this->aggregate($dictionaryA, $dictionaryB)->shouldBeLike(new Dictionary(['word' => 'word']));
    }
}
