<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Dictionary\SpellerDictionary;
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

final class AspellDictionary implements AspellDictionaryInterface
{
    private const ONE_DAY = 1;

    private FilesystemOperator $localFilesystem;
    private FilesystemOperator $sharedFilesystem;

    public function __construct(
        FilesystemProvider $filesystemProvider,
        private Clock $clock,
        private AspellDictionaryLocalFilesystemInterface $localFilesystemProvider,
        private SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->sharedFilesystem = $filesystemProvider->getFilesystem('dataQualityInsightsSharedAdapter');
        $this->localFilesystem = $localFilesystemProvider->getFilesystem();
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

        $this->sharedFilesystem->writeStream($this->getRelativeFilePath($languageCode), $putStream);

        if (is_resource($putStream)) {
            fclose($putStream);
        }
    }

    /**
     * @throws UnableToRetrieveDictionaryException
     */
    public function getUpToDateSpellerDictionary(LocaleCode $localeCode): ?SpellerDictionary
    {
        $languageCode = $this->supportedLocaleValidator->extractLanguageCode($localeCode);

        if ($languageCode === null || false === $this->isDictionaryExists($languageCode)) {
            return null;
        }

        $this->ensureDictionaryExistsLocally($languageCode);
        $this->ensureDictionaryIsUpToDate($languageCode);

        $dictionaryAbsolutePath =  rtrim($this->localFilesystemProvider->getAbsoluteRootPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($this->getRelativeFilePath($languageCode), DIRECTORY_SEPARATOR);

        return new SpellerDictionary($dictionaryAbsolutePath);
    }

    public function getSharedDictionaryTimestamp(LanguageCode $languageCode): ?int
    {
        if ($this->sharedFilesystem->fileExists($this->getRelativeFilePath($languageCode))) {
            return intval($this->sharedFilesystem->lastModified($this->getRelativeFilePath($languageCode)));
        }

        return null;
    }

    private function isDictionaryExists(LanguageCode $languageCode): bool
    {
        return true === $this->localFilesystem->fileExists($this->getRelativeFilePath($languageCode))
            || true === $this->sharedFilesystem->fileExists($this->getRelativeFilePath($languageCode));
    }

    /**
     * @throws UnableToRetrieveDictionaryException|FilesystemException
     */
    private function ensureDictionaryExistsLocally(LanguageCode $languageCode): void
    {
        if (false === $this->localFilesystem->fileExists($this->getRelativeFilePath($languageCode))
            && true === $this->sharedFilesystem->fileExists($this->getRelativeFilePath($languageCode))) {
            $this->downloadDictionaryFromSharedFilesystem($languageCode);
        }
    }

    /**
     * @throws UnableToRetrieveDictionaryException
     */
    private function ensureDictionaryIsUpToDate(LanguageCode $languageCode): void
    {
        if (false === $this->isDictionaryUpToDate($languageCode)) {
            $this->downloadDictionaryFromSharedFilesystem($languageCode);
        }
    }

    private function isDictionaryUpToDate(LanguageCode $languageCode): bool
    {
        if (false === $this->localFilesystem->fileExists($this->getRelativeFilePath($languageCode))) {
            return false;
        }

        $localDictionaryTimestamp = $this->localFilesystem->lastModified($this->getRelativeFilePath($languageCode));

        $fileDate = $this->clock->fromTimestamp(intval($localDictionaryTimestamp));
        $now = $this->clock->getCurrentTime();
        $interval = $fileDate->diff($now);

        if ($interval->format('%a') <= self::ONE_DAY) {
            return true;
        }

        if (false === $this->sharedFilesystem->fileExists($this->getRelativeFilePath($languageCode))) {
            return false;
        }

        return ! ($this->sharedFilesystem->lastModified($this->getRelativeFilePath($languageCode)) > $localDictionaryTimestamp);
    }

    private function downloadDictionaryFromSharedFilesystem(LanguageCode $languageCode): void
    {
        $dictionaryRelativePath = $this->getRelativeFilePath($languageCode);

        if (false === $this->sharedFilesystem->fileExists($dictionaryRelativePath)) {
            throw new UnableToRetrieveDictionaryException($languageCode, sprintf('No shared file found for "%s"', $dictionaryRelativePath));
        }

        $readStream = $this->sharedFilesystem->readStream($dictionaryRelativePath);

        if (!is_resource($readStream)) {
            throw new UnableToRetrieveDictionaryException($languageCode, sprintf('Read stream "%s" failed', $dictionaryRelativePath));
        }

        $this->localFilesystem->writeStream($dictionaryRelativePath, $readStream);

        if (false === $this->localFilesystem->fileExists($dictionaryRelativePath)) {
            throw new UnableToRetrieveDictionaryException($languageCode, sprintf('Write local file "%s" failed', $dictionaryRelativePath));
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

    private function getRelativeFilePath(LanguageCode $languageCode): string
    {
        return sprintf(
            'consistency/text_checker/aspell/custom-dictionary-%s.pws',
            $languageCode->__toString()
        );
    }
}
