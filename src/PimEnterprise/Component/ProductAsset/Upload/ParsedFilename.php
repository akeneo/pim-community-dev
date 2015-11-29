<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ProductAsset\Upload;

use Pim\Component\Catalog\Model\LocaleInterface;

/**
 * @see FilenameParserInterface
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
class ParsedFilename implements ParsedFilenameInterface
{
    const PATTERN_CODE = '[_a-zA-Z0-9-]+';

    /** @var string */
    protected $rawFilename;

    /** @var string */
    protected $assetCode;

    /** @var string */
    protected $localeCode;

    /** @var string */
    protected $extension;

    /** @var LocaleInterface[] */
    protected $availableLocales;

    /**
     * @param LocaleInterface[] $availableLocales
     * @param                   $rawFilename
     */
    public function __construct(array $availableLocales, $rawFilename)
    {
        $this->rawFilename      = $rawFilename;
        $this->availableLocales = $availableLocales;

        $this->parseRawFilename($this->rawFilename);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawFilename()
    {
        return $this->rawFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetCode()
    {
        return $this->assetCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function getCleanFilename()
    {
        if (null === $this->getAssetCode()) {
            return null;
        } elseif (null === $this->getLocaleCode()) {
            return sprintf('%s.%s', $this->getAssetCode(), $this->getExtension());
        } else {
            return sprintf('%s-%s.%s', $this->getAssetCode(), $this->getLocaleCode(), $this->getExtension());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseRawFilename($rawFilename)
    {
        $localeCodes   = array_map(function (LocaleInterface $locale) {
            return $locale->getCode();
        }, $this->availableLocales);
        $patternLocale = join('|', $localeCodes);

        $pattern = sprintf('/^
            (?:
                (?P<code1>%s)        #asset code
                -(?P<locale>%s)     #locale code (optionnal)
            |
                (?P<code2>%s)        #asset code
            )
            \.(?P<extension>[^.]+)   #file extension
            $/x', static::PATTERN_CODE, $patternLocale, static::PATTERN_CODE);

        if (preg_match($pattern, $rawFilename, $matches)) {
            if (strlen($matches['code1']) > 0) {
                $this->assetCode = $this->sanitizeAssetCode($matches['code1']);
            } elseif (strlen($matches['code2']) > 0) {
                $this->assetCode = $this->sanitizeAssetCode($matches['code2']);
            }
            $this->localeCode = strlen($matches['locale']) > 0 ? $matches['locale'] : null;
            $this->extension  = $matches['extension'];
        }
    }

    /**
     * @param $code
     *
     * @return string
     */
    protected function sanitizeAssetCode($code)
    {
        return str_replace('-', '_', trim($code));
    }
}
