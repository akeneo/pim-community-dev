<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\DictionaryNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use League\Flysystem\MountManager;

class AspellDictionary
{
    private const ONE_DAY = 1;

    /** @var MountManager */
    private $mountManager;

    /** @var Clock */
    private $clock;

    public function __construct(MountManager $mountManager, Clock $clock)
    {
        $this->mountManager = $mountManager;
        $this->clock = $clock;
    }

    public function persistDictionaryToSharedFilesystem(Dictionary $dictionary, LanguageCode $languageCode)
    {
        $putStream = tmpfile();

        if(!is_resource($putStream))
        {
            throw new \RuntimeException('Unable to create temporary file');
        }

        fwrite($putStream, $this->dictionaryHeader($dictionary, $languageCode) . PHP_EOL);

        foreach ($dictionary as $word) {
            fwrite($putStream, $word . PHP_EOL);
        }

        rewind($putStream);

        $this->mountManager->getFilesystem('dataQualityInsightsSharedAdapter')->putStream($this->getRelativeFilePath($languageCode), $putStream);

        if (is_resource($putStream)) {
            fclose($putStream);
        }
    }

    /**
     * @throws DictionaryNotFoundException
     */
    public function getUpToDateLocalDictionaryRelativeFilePath(LocaleCode $localeCode): string
    {
        $languageCode = $this->extractLanguageCode($localeCode);

        $this->ensureDictionaryExistsLocally($languageCode);
        $this->ensureDictionaryIsUpToDate($languageCode);

        return $this->getRelativeFilePath($languageCode);
    }

    public function getSharedDictionaryTimestamp(LanguageCode $languageCode): ?int
    {
        if ($this->mountManager->has($this->getSharedAdapterFilePath($languageCode))) {
            return intval($this->mountManager->getTimestamp($this->getSharedAdapterFilePath($languageCode)));
        }

        return null;
    }

    /**
     * @throws DictionaryNotFoundException
     */
    private function ensureDictionaryExistsLocally(LanguageCode $languageCode): void
    {
        if (false === $this->mountManager->has($this->getLocalAdapterFilePath($languageCode))
            && true === $this->mountManager->has($this->getSharedAdapterFilePath($languageCode))) {
            $this->downloadDictionaryFromSharedFilesystem($languageCode);
        }
    }

    /**
     * @throws DictionaryNotFoundException
     */
    private function ensureDictionaryIsUpToDate(LanguageCode $languageCode): void
    {
        if (false === $this->isDictionaryUpToDate($languageCode)) {
            $this->downloadDictionaryFromSharedFilesystem($languageCode);
        }
    }

    private function isDictionaryUpToDate(LanguageCode $languageCode): bool
    {
        $localDictionaryTimestamp = $this->mountManager->getTimestamp($this->getLocalAdapterFilePath($languageCode));

        $fileDate = $this->clock->fromTimestamp(intval($localDictionaryTimestamp));
        $now = $this->clock->getCurrentTime();
        $interval = $fileDate->diff($now);

        if ($interval->format('%a') <= self::ONE_DAY) {
            return true;
        }

        return ! ($this->mountManager->getTimestamp($this->getSharedAdapterFilePath($languageCode)) > $localDictionaryTimestamp);
    }

    private function downloadDictionaryFromSharedFilesystem(LanguageCode $languageCode): void
    {
        $readStream = $this->mountManager->readStream($this->getSharedAdapterFilePath($languageCode));

        if(!is_resource($readStream))
        {
            throw new DictionaryNotFoundException();
        }

        $this->mountManager->putStream(
            $this->getLocalAdapterFilePath($languageCode),
            $readStream
        );

        if (false === $this->mountManager->has($this->getLocalAdapterFilePath($languageCode))) {
            throw new DictionaryNotFoundException();
        }
    }

    private function dictionaryHeader(Dictionary $dictionary, LanguageCode $languageCode): string
    {
        return sprintf(
            'personal_ws-1.1 %s %d',
            $languageCode->__toString(),
            count($dictionary)
        );
    }

    private function extractLanguageCode(LocaleCode $localeCode): LanguageCode
    {
        preg_match('~^(?<language_code>[a-z]{2})_[A-Z]{2}$~', $localeCode->__toString(), $matches);

        return new LanguageCode($matches['language_code']);
    }

    private function getRelativeFilePath(LanguageCode $languageCode): string
    {
        return sprintf(
            'consistency/text_checker/aspell/custom-dictionary-%s.pws',
            $languageCode->__toString()
        );
    }

    private function getLocalAdapterFilePath(LanguageCode $languageCode): string
    {
        return 'dataQualityInsightsLocalAdapter://' . $this->getRelativeFilePath($languageCode);
    }

    private function getSharedAdapterFilePath(LanguageCode $languageCode): string
    {
        return 'dataQualityInsightsSharedAdapter://' . $this->getRelativeFilePath($languageCode);
    }
}
