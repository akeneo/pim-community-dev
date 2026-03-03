<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Read\WrongCredentialsCombinations;
use Akeneo\Connectivity\Connection\Domain\WrongCredentialsConnection\Model\Write\WrongCredentialsCombination;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WrongCredentialsCombinationRepositoryInterface
{
    public function create(WrongCredentialsCombination $wrongCredentialsCombination): void;

    public function findAll(\DateTimeImmutable $since): WrongCredentialsCombinations;
}
