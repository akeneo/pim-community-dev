<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Consistency\TextChecker;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\DictionaryNotFoundException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;

final class AspellDictionary implements AspellDictionaryInterface
{
    private const ONE_DAY = 1;

    /** @var MountManager */
    private $mountManager;

    /** @var Clock */
    private $clock;

    /** @var Filesystem */
    private $localFilesystem;

    /** @var FilesystemInterface */
    private $sharedFilesystem;

    /** @var AspellDictionaryLocalFilesystemInterface */
    private $localFilesystemProvider;

    public function __construct(MountManager $mountManager, Clock $clock, AspellDictionaryLocalFilesystemInterface $localFilesystemProvider)
    {
        $this->mountManager = $mountManager;
        $this->clock = $clock;

        $this->sharedFilesystem = $mountManager->getFilesystem('dataQualityInsightsSharedAdapter');
        $this->localFilesystem = $localFilesystemProvider->getFilesystem();
        $this->localFilesystemProvider = $localFilesystemProvider;
    }

    public function persistDictionaryToSharedFilesystem(Dictionary $dictionary, LanguageCode $languageCode): void
    {
        $putStream = tmpfile();

        if (!is_resource($putStream)) {
            throw new \RuntimeException('Unable to create temporary file');
        }

        fwrite($putStream, $this->dictionaryHeader($dictionary, $languageCode) . PHP_EOL);

        foreach ($dictionary as $word) {
            fwrite($putStream, $word . PHP_EOL);
        }

        rewind($putStream);

        $this->sharedFilesystem->putStream($this->getRelativeFilePath($languageCode), $putStream);

        if (is_resource($putStream)) {
            fclose($putStream);
        }
    }

    /**
     * @throws DictionaryNotFoundException
     */
    public function getUpToDateLocalDictionaryAbsoluteFilePath(LocaleCode $localeCode): string
    {
        $languageCode = $this->extractLanguageCode($localeCode);

        $this->ensureDictionaryExistsLocally($languageCode);
        $this->ensureDictionaryIsUpToDate($languageCode);

        return rtrim($this->localFilesystemProvider->getAbsoluteRootPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($this->getRelativeFilePath($languageCode), DIRECTORY_SEPARATOR);
    }

    public function getSharedDictionaryTimestamp(LanguageCode $languageCode): ?int
    {
        if ($this->sharedFilesystem->has($this->getRelativeFilePath($languageCode))) {
            return intval($this->sharedFilesystem->getTimestamp($this->getRelativeFilePath($languageCode)));
        }

        return null;
    }

    /**
     * @throws DictionaryNotFoundException
     */
    private function ensureDictionaryExistsLocally(LanguageCode $languageCode): void
    {
        if (false === $this->localFilesystem->has($this->getRelativeFilePath($languageCode))
            && true === $this->sharedFilesystem->has($this->getRelativeFilePath($languageCode))) {
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
        if (false === $this->localFilesystem->has($this->getRelativeFilePath($languageCode))) {
            return false;
        }

        $localDictionaryTimestamp = $this->localFilesystem->getTimestamp($this->getRelativeFilePath($languageCode));

        $fileDate = $this->clock->fromTimestamp(intval($localDictionaryTimestamp));
        $now = $this->clock->getCurrentTime();
        $interval = $fileDate->diff($now);

        if ($interval->format('%a') <= self::ONE_DAY) {
            return true;
        }

        if (false === $this->sharedFilesystem->has($this->getRelativeFilePath($languageCode))) {
            return false;
        }

        return ! ($this->sharedFilesystem->getTimestamp($this->getRelativeFilePath($languageCode)) > $localDictionaryTimestamp);
    }

    private function downloadDictionaryFromSharedFilesystem(LanguageCode $languageCode): void
    {
        if (false === $this->sharedFilesystem->has($this->getRelativeFilePath($languageCode))) {
            throw new DictionaryNotFoundException();
        }

        $readStream = $this->sharedFilesystem->readStream($this->getRelativeFilePath($languageCode));

        if (!is_resource($readStream)) {
            throw new DictionaryNotFoundException();
        }

        $this->localFilesystem->putStream(
            $this->getRelativeFilePath($languageCode),
            $readStream
        );

        if (false === $this->localFilesystem->has($this->getRelativeFilePath($languageCode))) {
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
}
