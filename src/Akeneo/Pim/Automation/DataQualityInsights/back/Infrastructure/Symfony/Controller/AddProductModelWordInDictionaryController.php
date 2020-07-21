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

use Akeneo\Pim\Automation\DataQualityInsights\Application\FeatureFlag;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\Dictionary\IgnoreWordForProductModel;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddProductModelWordInDictionaryController
{
    /** @var IgnoreWordForProductModel */
    private $ignoreWordForProductModel;

    /** @var FeatureFlag */
    private $featureFlag;

    public function __construct(IgnoreWordForProductModel $ignoreWordForProductModel, FeatureFlag $featureFlag)
    {
        $this->ignoreWordForProductModel = $ignoreWordForProductModel;
        $this->featureFlag = $featureFlag;
    }

    public function __invoke(Request $request)
    {
        if (!$this->featureFlag->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        try {
            $word = new DictionaryWord($request->request->get('word'));
            $localeCode = new LocaleCode($request->request->get('locale'));
            $productId = new ProductId($request->request->getInt('product_id'));

            $this->ignoreWordForProductModel->execute($word, $localeCode, $productId);

            return new Response(null, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
