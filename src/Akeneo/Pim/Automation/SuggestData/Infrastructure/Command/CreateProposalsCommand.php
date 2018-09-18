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

use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Proposal\Command\CreateProposalHandler;
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

    /** @var CreateProposalHandler */
    private $handler;

    /**
     * @param CreateProposalHandler $handler
     */
    public function __construct(
        CreateProposalHandler $handler
    ) {
        $this->handler = $handler;
        parent::__construct();
    }

    /**
     * {@inheritdoc
     */
    protected function configure()
    {
        $this->setDescription('Handles the creation of proposals based on suggested data');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handler->handle(new CreateProposalCommand());
        $output->writeln('<info>Proposals sucessfully created</info>');
    }
}
