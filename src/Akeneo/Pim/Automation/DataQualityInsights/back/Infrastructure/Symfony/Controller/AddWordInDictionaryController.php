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
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository\TextCheckerDictionaryRepositoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\DictionaryWord;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Events\WordIgnoredEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AddWordInDictionaryController
{
    /**
     * @var FeatureFlag
     */
    private $featureFlag;

    /**
     * @var SupportedLocaleValidator
     */
    private $supportedLocaleValidator;

    /**
     * @var TextCheckerDictionaryRepositoryInterface
     */
    private $textCheckerDictionaryRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        FeatureFlag $featureFlag,
        SupportedLocaleValidator $supportedLocaleValidator,
        TextCheckerDictionaryRepositoryInterface $textCheckerDictionaryRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->featureFlag = $featureFlag;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
        $this->textCheckerDictionaryRepository = $textCheckerDictionaryRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(Request $request)
    {
        try {
            $word = new DictionaryWord($request->request->get('word'));
            $localeCode = new LocaleCode($request->request->get('locale'));
            $productId = new ProductId($request->request->getInt('product_id'));

            if (!$this->supportedLocaleValidator->isSupported($localeCode)) {
                throw new \InvalidArgumentException('Unable to process locales that are not handled by spellchecker');
            }

            $dictionaryWord = new Write\TextCheckerDictionaryWord($localeCode, $word);
            $this->textCheckerDictionaryRepository->save($dictionaryWord);

            $this->eventDispatcher->dispatch(new WordIgnoredEvent($productId), WordIgnoredEvent::WORD_IGNORED);

            return new Response(null, Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return new Response(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
