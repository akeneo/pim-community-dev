<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider;

/**
 * SuggestedDataInterface factory.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestedDataFactory
{
    /**
     * @param string $subscriptionIdentifier
     * @param array $codes
     * @param array $attributes
     *
     * @return SuggestedDataInterface
     */
    public function create(string $subscriptionIdentifier, array $codes, array $attributes): SuggestedDataInterface
    {
        return new SuggestedData($subscriptionIdentifier, $codes, $attributes);
    }
}
