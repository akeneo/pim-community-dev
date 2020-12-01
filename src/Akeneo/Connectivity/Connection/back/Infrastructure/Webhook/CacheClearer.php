<?php
declare(strict_types=1);

namespace AkeneoEnterprise\Connectivity\Connection\Infrastructure\Webhook;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Pim\Permission\Bundle\Persistence\Sql\LocaleRight\GetAllViewableLocalesForUser;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CacheClearer implements CacheClearerInterface
{
    private CacheClearerInterface $communityCacheClearer;

    private GetAllViewableLocalesForUser $getAllViewableLocalesForUser;

    public function __construct(
        CacheClearerInterface $communityCacheClearer,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser
    ) {
        $this->communityCacheClearer = $communityCacheClearer;
        $this->getAllViewableLocalesForUser = $getAllViewableLocalesForUser;
    }

    public function clear(): void
    {
        $this->communityCacheClearer->clear();
        $this->getAllViewableLocalesForUser->clearCache();
    }
}
