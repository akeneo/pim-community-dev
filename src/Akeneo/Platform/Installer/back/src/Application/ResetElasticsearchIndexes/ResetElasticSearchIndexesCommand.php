<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\ResetElasticsearchIndexes;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ResetElasticSearchIndexesCommand
{
    public function __construct(
        private readonly SymfonyStyle $io
    ) {}

    public function getIo(): SymfonyStyle {
        return $this->io;
    }
}
