<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Api;

use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Bundle\ApiBundle\Cache\WarmupQueryCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class WarmupGetViewableAttributeCodesQuery implements WarmupQueryCache
{
    /** @var GetViewableAttributeCodesForUserInterface */
    private $getViewableAttributeCodesForUser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->getViewableAttributeCodesForUser = $getViewableAttributeCodesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function fromRequest(Request $request): void
    {
        $userId = $this->getUserId();
        if (null === $this->getUserId()) {
            return;
        }

        $attributeCodes = $this->getAttributeCodesFromValues($request->getContent());
        $this->getViewableAttributeCodesForUser->forAttributeCodes($attributeCodes, $userId);
    }

    /**
     * @param string $content
     *
     * @return string[]
     */
    private function getAttributeCodesFromValues(string $content): array
    {
        $attributeCodes = [];

        $lines = explode(PHP_EOL, $content);
        foreach ($lines as $line) {
            $decodedLine = json_decode($line, true);

            $values = null !== $decodedLine && isset($decodedLine['values']) && is_array($decodedLine['values']) ?
                $decodedLine['values'] :
                [];
            $attributeCodes[] = array_keys($values);
        }
        return array_values(array_unique(array_merge_recursive(... $attributeCodes)));
    }

    private function getUserId(): ?int
    {
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()->getUser()) {
            return $this->tokenStorage->getToken()->getUser()->getId();
        }

        return null;
    }
}
