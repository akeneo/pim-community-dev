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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping;

/**
 * Fake identifiers mapping API.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingApiFake implements IdentifiersMappingApiInterface
{
    /** @var array */
    private $identifiersMapping;

    public function __construct()
    {
        $this->identifiersMapping = [];
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $identifiersMapping): void
    {
        $this->identifiersMapping = $identifiersMapping;
    }

    /**
     * Returns the stored identifiers mapping.
     * This method is only used for testing purpose, and so is not present in the interface.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->identifiersMapping;
    }
}
