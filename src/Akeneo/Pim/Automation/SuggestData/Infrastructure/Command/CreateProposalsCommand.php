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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsCommand as AppCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalsHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class CreateProposalsCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'pimee:suggest-data:create-proposals';

    /** @var CreateProposalsHandler */
    private $handler;

    /**
     * @param CreateProposalsHandler $handler
     */
    public function __construct(
        CreateProposalsHandler $handler
    ) {
        $this->handler = $handler;
        parent::__construct();
    }

    /**
     * {@inheritdoc.
     */
    protected function configure(): void
    {
        $this->setDescription('Handles the creation of proposals based on suggested data');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->handler->handle(new AppCommand());
        $output->writeln('<info>Proposals sucessfully created</info>');
    }
}
