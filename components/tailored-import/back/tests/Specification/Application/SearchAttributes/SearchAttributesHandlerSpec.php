<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredImport\Application\SearchAttributes;

use Akeneo\Platform\TailoredImport\Application\SearchAttributes\SearchAttributesQuery;
use Akeneo\Platform\TailoredImport\Domain\Query\Attribute\FindAttributeTranslationsInterface;
use PhpSpec\ObjectBehavior;

class SearchAttributesHandlerSpec extends ObjectBehavior
{
    public function it_can_search_attributes_on_their_translations(
        FindAttributeTranslationsInterface $findAttributeTranslations,
    ) {
        $this->beConstructedWith($findAttributeTranslations);

        $query = new SearchAttributesQuery();
        $query->attributeCodes = ['sku', 'name', 'description'];
        $query->localeCode = 'en_US';

        $findAttributeTranslations->byAttributeCodesAndLocaleCode($query->attributeCodes, $query->localeCode)
            ->willReturn([
                'sku' => 'identifier',
                'description' => 'nice text here',
                'name' => 'full name and not description',
            ]);

        $query->search = 'DESCR';
        $this->handle($query)->shouldReturn(['name', 'description']);

        $query->search = 'name';
        $this->handle($query)->shouldReturn(['name']);

        $query->search = 'SKU';
        $this->handle($query)->shouldReturn(['sku']);

        $query->search = 'not found';
        $this->handle($query)->shouldReturn([]);
    }
}
