<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model;

use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Webmozart\Assert\Assert;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class LocaleIdentifierCollection implements \IteratorAggregate
{
    /** @var LocaleIdentifier[] */
    private $localeIdentifiers;

    /**
     * @param LocaleIdentifier[] $localeIdentifiers
     */
    public function __construct(array $localeIdentifiers)
    {
        Assert::allIsInstanceOf($localeIdentifiers, LocaleIdentifier::class);
        $this->localeIdentifiers = $localeIdentifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->localeIdentifiers);
    }

    public function isEmpty(): bool
    {
        return empty($this->localeIdentifiers);
    }

    /**
     * @return string[]
     */
    public function normalize(): array
    {
        return array_map(function (LocaleIdentifier $localeIdentifier) {
            return $localeIdentifier->normalize();
        }, $this->localeIdentifiers);
    }

    /**
     * @param string[] $normalizedLocaleIdentifiers
     */
    public static function fromNormalized(array $normalizedLocaleIdentifiers): LocaleIdentifierCollection
    {
        $localeIdentifiers = array_map(function (string $localeIdentifier) {
            return LocaleIdentifier::fromCode($localeIdentifier);
        }, $normalizedLocaleIdentifiers);

        return new self($localeIdentifiers);
    }

    /**
     * @param LocaleReference[] $localeReferences
     */
    public static function fromLocaleReferences(array $localeReferences): LocaleIdentifierCollection
    {
        $localeIdentifiers = array_reduce($localeReferences, function ($localeIdentifiers, LocaleReference $localeReference) {
            if (!$localeReference->isEmpty()) {
                $localeIdentifiers[] = $localeReference->getIdentifier();
            }

            return $localeIdentifiers;
        }, []);

        return new self($localeIdentifiers);
    }
}
