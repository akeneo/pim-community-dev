<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Job;

use Akeneo\ReferenceEntity\Application\Record\MassDeleteRecords\MassDeleteRecordsLauncherInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 */
class MassDeleteRecordsLauncher implements MassDeleteRecordsLauncherInterface
{
    private PublishJobToQueue $publishJobToQueue;
    private TokenStorageInterface $tokenStorage;

    public function __construct(PublishJobToQueue $publishJobToQueue, TokenStorageInterface $tokenStorage)
    {
        $this->publishJobToQueue = $publishJobToQueue;
        $this->tokenStorage = $tokenStorage;
    }

    public function launchForReferenceEntityAndQuery(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        RecordQuery $recordQuery
    ): void {
        $token = $this->tokenStorage->getToken();
        $username = null !== $token ? $token->getUsername() : null;

        $config = [
            'reference_entity_identifier' => (string) $referenceEntityIdentifier,
            'query' => $recordQuery->normalize(),
            'user_to_notify' => $username
        ];

        $this->publishJobToQueue->publish('reference_entity_mass_delete_records', $config, false, $username);
    }
}
