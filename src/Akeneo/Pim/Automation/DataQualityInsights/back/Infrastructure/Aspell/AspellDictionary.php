<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Application\Spellcheck\SupportedLocaleValidator;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\UnableToRetrieveDictionaryException;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Dictionary;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LanguageCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Mekras\Speller;

final class AspellDictionary implements AspellDictionaryInterface
{
    private const ONE_DAY = 1;

    /** @var MountManager */
    private $mountManager;

    /** @var Clock */
    private $clock;

    /** @var FilesystemInterface */
    private $localFilesystem;

    /** @var FilesystemInterface */
    private $sharedFilesystem;

    /** @var AspellDictionaryLocalFilesystemInterface */
    private $localFilesystemProvider;

    /** @var SupportedLocaleValidator */
    private $supportedLocaleValidator;

    public function __construct(
        MountManager $mountManager,
        Clock $clock,
        AspellDictionaryLocalFilesystemInterface $localFilesystemProvider,
        SupportedLocaleValidator $supportedLocaleValidator
    ) {
        $this->mountManager = $mountManager;
        $this->clock = $clock;

        $this->sharedFilesystem = $mountManager->getFilesystem('dataQualityInsightsSharedAdapter');
        $this->localFilesystem = $localFilesystemProvider->getFilesystem();
        $this->localFilesystemProvider = $localFilesystemProvider;
        $this->supportedLocaleValidator = $supportedLocaleValidator;
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
     * @throws UnableToRetrieveDictionaryException
     */
    public function getUpToDateSpellerDictionary(LocaleCode $localeCode): ?Speller\Dictionary
    {
        $languageCode = $this->supportedLocaleValidator->extractLanguageCode($localeCode);

        if ($languageCode === null || false === $this->isDictionaryExists($languageCode)) {
            return null;
        }

        $this->ensureDictionaryExistsLocally($languageCode);
        $this->ensureDictionaryIsUpToDate($languageCode);

        $dictionaryAbsolutePath =  rtrim($this->localFilesystemProvider->getAbsoluteRootPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($this->getRelativeFilePath($languageCode), DIRECTORY_SEPARATOR);

        return new Speller\Dictionary($dictionaryAbsolutePath);
    }

    public function getSharedDictionaryTimestamp(LanguageCode $languageCode): ?int
    {
        if ($this->sharedFilesystem->has($this->getRelativeFilePath($languageCode))) {
            return intval($this->sharedFilesystem->getTimestamp($this->getRelativeFilePath($languageCode)));
        }

        return null;
    }

    private function isDictionaryExists(LanguageCode $languageCode): bool
    {
        return true === $this->localFilesystem->has($this->getRelativeFilePath($languageCode))
            || true === $this->sharedFilesystem->has($this->getRelativeFilePath($languageCode));
    }

    /**
     * @throws UnableToRetrieveDictionaryException
     */
    private function ensureDictionaryExistsLocally(LanguageCode $languageCode): void
    {
        if (false === $this->localFilesystem->has($this->getRelativeFilePath($languageCode))
            && true === $this->sharedFilesystem->has($this->getRelativeFilePath($languageCode))) {
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
        $dictionaryRelativePath = $this->getRelativeFilePath($languageCode);

        if (false === $this->sharedFilesystem->has($dictionaryRelativePath)) {
            throw new UnableToRetrieveDictionaryException($languageCode, sprintf('No shared file found for "%s"', $dictionaryRelativePath));
        }

        $readStream = $this->sharedFilesystem->readStream($dictionaryRelativePath);

        if (!is_resource($readStream)) {
            throw new UnableToRetrieveDictionaryException($languageCode, sprintf('Read stream "%s" failed', $dictionaryRelativePath));
        }

        $this->localFilesystem->putStream($dictionaryRelativePath, $readStream);

        if (false === $this->localFilesystem->has($dictionaryRelativePath)) {
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
