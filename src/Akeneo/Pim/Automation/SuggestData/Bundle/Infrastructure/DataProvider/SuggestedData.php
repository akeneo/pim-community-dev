<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Bundle\Infrastructure\DataProvider;

/**
 * Represents suggested data from PIM.ai
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SuggestedData implements SuggestedDataInterface
{
    /** @var string */
    private $subscriptionIdentifier;

    /** @var array */
    private $codes;

    /** @var array */
    private $attributes;

    /**
     * @param string $subscriptionIdentifier
     * @param array  $codes
     * @param array  $attributes
     */
    public function __construct(string $subscriptionIdentifier, array $codes, array $attributes)
    {
        $this->subscriptionIdentifier = $subscriptionIdentifier; // TODO validation
        $this->codes = $codes; // TODO validation
        $this->attributes = $attributes; // TODO validation
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscriptionIdentifier(): string
    {
        return $this->subscriptionIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggestedCodes(): array
    {
        return $this->codes;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggestedAttributes(): array
    {
        return $this->attributes;
    }
}
