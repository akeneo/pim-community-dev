<?php
declare(strict_types=1);

namespace PimEnterprise\Bundle\SuggestDataBundle\Infra\DataProvider;

/**
 * Object representing suggested data from PIM.ai.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
interface SuggestedDataInterface
{
    /**
     * @return string
     */
    public function getSubscriptionIdentifier(): string;

    /**
     * @return array
     */
    public function getSuggestedCodes(): array;

    /**
     * @return array
     */
    public function getSuggestedAttributes(): array;
}
