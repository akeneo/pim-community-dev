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

namespace spec\Akeneo\ReferenceEntity\Domain\Model;

use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use PhpSpec\ObjectBehavior;

class LocaleIdentifierCollectionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([]);
    }

    function it_is_an_iterator_aggregate()
    {
        $this->shouldImplement(\IteratorAggregate::class);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(LocaleIdentifierCollection::class);
    }

    public function it_returns_true_if_the_collection_is_empty()
    {
        $this->isEmpty()->shouldReturn(true);
    }

    public function it_returns_false_if_the_collection_is_not_empty()
    {
        $this->beConstructedWith([LocaleIdentifier::fromCode('en_US')]);
        $this->isEmpty()->shouldReturn(false);
    }

    public function it_normalizes_itself()
    {
        $this->beConstructedWith([
            LocaleIdentifier::fromCode('en_US'),
            LocaleIdentifier::fromCode('fr_FR')
        ]);

        $this->normalize()->shouldReturn(['en_US', 'fr_FR']);
    }

    public function it_can_be_created_from_normalized_locale_identifiers()
    {
        $this->beConstructedThrough('fromNormalized', [['en_US', 'fr_FR']]);
        $this->normalize()->shouldReturn(['en_US', 'fr_FR']);
    }

    public function it_can_be_create_from_a_list_of_locale_references()
    {
        $this->beConstructedThrough('fromLocaleReferences', [[
            LocaleReference::createFromNormalized('en_US'),
            LocaleReference::createFromNormalized('fr_FR'),
            LocaleReference::noReference()
        ]]);

        $this->normalize()->shouldReturn(['en_US', 'fr_FR']);
    }
}
