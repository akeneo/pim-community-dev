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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Controller;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary\IgnoreWordForAttribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddAttributeWordInDictionaryController
{
    /** @var IgnoreWordForAttribute */
    private $ignoreWordForAttribute;

    public function __construct(IgnoreWordForAttribute $ignoreWordForAttribute)
    {
        $this->ignoreWordForAttribute = $ignoreWordForAttribute;
    }

    public function __invoke(Request $request)
    {
        try {
            $word = new DictionaryWord($request->request->get('word'));
            $localeCode = new LocaleCode($request->request->get('locale'));
            $attributeCode = $request->request->get('attribute_code');

            if (empty($attributeCode)) {
                $this->ignoreWordForAttribute->execute($word, $localeCode, null);
            } else {
                $attributeCode = new AttributeCode($attributeCode);
                $this->ignoreWordForAttribute->execute($word, $localeCode, $attributeCode);
            }


            return new Response(null, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
