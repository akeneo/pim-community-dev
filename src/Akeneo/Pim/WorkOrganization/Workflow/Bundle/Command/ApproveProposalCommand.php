<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\EntityWithValuesDraftManager;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Approve a proposal
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ApproveProposalCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:proposal:approve')
            ->setDescription('Approve a proposal')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'The author of the proposal'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $username = $input->getArgument('username');

        $product = $this->getProductRepository()->findOneByIdentifier($identifier);
        if (false === $product) {
            $output->writeln(sprintf('<error>The product with identifier "%s" not found<error>', $identifier));

            return -1;
        }

        $proposal = $this->getProductDraftRepository()->findUserEntityWithValuesDraft($product, $username);
        if (null === $proposal) {
            $output->writeln(sprintf(
                '<error>Proposal with identifier "%s" and user "%s" not found<error>',
                $identifier,
                $username
            ));

            return -1;
        }

        if ($proposal->getStatus() === EntityWithValuesDraftInterface::READY) {
            $this->getProductDraftManager()->approve($proposal);
            $output->writeln(sprintf('<info>Proposal "%s" has been approved<info>', $identifier));

            return null;
        } else {
            $output->writeln(sprintf(
                '<error>Proposal with identifier "%s" and user "%s" is not ready<error>',
                $identifier,
                $username
            ));

            return -1;
        }
    }

    /**
     * @return ProductRepositoryInterface
     */
    protected function getProductRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.product');
    }

    /**
     * @return EntityWithValuesDraftRepositoryInterface
     */
    protected function getProductDraftRepository()
    {
        return $this->getContainer()->get('pimee_workflow.repository.product_draft');
    }

    /**
     * @return EntityWithValuesDraftManager
     */
    protected function getProductDraftManager()
    {
        return $this->getContainer()->get('pimee_workflow.manager.product_draft');
    }
}
