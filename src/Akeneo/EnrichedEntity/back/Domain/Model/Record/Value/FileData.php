<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Record\Value;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class FileData implements ValueDataInterface
{
    private const KEY = 'key';
    private const ORIGINAL_FILENAME = 'originalFilename';

    /** @var string */
    private $key;

    /** @var string */
    private $originalFilename;

    private function __construct(string $key, string $originalFilename)
    {
        Assert::stringNotEmpty($key, 'File data key cannot be empty');
        Assert::stringNotEmpty($originalFilename, 'Original filename data cannot be empty');

        $this->key = $key;
        $this->originalFilename = $originalFilename;
    }

    /**
     * @return array
     */
    public function normalize()
    {
        return [
            self::KEY => $this->key,
            self::ORIGINAL_FILENAME => $this->originalFilename
        ];
    }

    public static function createFromFileinfo(FileInfoInterface $fileInfo): ValueDataInterface
    {
        return new self($fileInfo->getKey(), $fileInfo->getOriginalFilename());
    }

    public static function createFromNormalize($normalizedData): ValueDataInterface
    {
        Assert::isArray($normalizedData, 'Normalized data should be an array');
        Assert::keyExists($normalizedData, self::KEY, sprintf(
            'The key "%s" should be present in the normalized data', self::KEY
        ));
        Assert::keyExists($normalizedData, self::ORIGINAL_FILENAME, sprintf(
            'The key "%s" should be present in the normalized data', self::ORIGINAL_FILENAME
        ));

        return new self($normalizedData[self::KEY], $normalizedData[self::ORIGINAL_FILENAME]);
    }
}
