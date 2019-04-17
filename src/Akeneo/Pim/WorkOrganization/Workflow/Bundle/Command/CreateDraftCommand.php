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

use Akeneo\Pim\Enrichment\Bundle\Command\UpdateProductCommand;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a draft
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class CreateDraftCommand extends UpdateProductCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $updatesExample = [
            [
                'type'  => 'set_data',
                'field' => 'name',
                'data'  => 'My name',
            ],
            [
                'type'        => 'copy_data',
                'from_field'  => 'description',
                'from_scope'  => 'ecommerce',
                'from_locale' => 'en_US',
                'to_field'    => 'description',
                'to_scope'    => 'mobile',
                'to_locale'   => 'en_US',
            ],
            [
                'type'  => 'add_data',
                'field' => 'categories',
                'data'  => ['tshirt'],
            ],
        ];

        $this
            ->setName('pim:draft:create')
            ->setDescription('Create a draft based on a diff from the original product')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            )
            ->addArgument(
                'json_updates',
                InputArgument::REQUIRED,
                sprintf("The product updates in json, for instance, '%s'", json_encode($updatesExample))
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                sprintf('The author of updated product')
            )
            ->addArgument(
                'draft_status',
                InputArgument::OPTIONAL,
                sprintf(
                    "The product draft status, for instance, '%s', '%s'",
                    EntityWithValuesDraftInterface::IN_PROGRESS,
                    EntityWithValuesDraftInterface::READY
                ),
                (string) EntityWithValuesDraftInterface::IN_PROGRESS
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $entityWithValues = $this->getProduct($identifier);
        if (null === $entityWithValues) {
            $output->writeln(sprintf('<error>Product with identifier "%s" not found</error>', $identifier));

            return -1;
        }

        $username = $input->getArgument('username');
        if (!$this->createToken($output, $username)) {
            return -1;
        }

        $updates = json_decode($input->getArgument('json_updates'), true);
        $this->update($entityWithValues, $updates);

        $violations = $this->validate($entityWithValues);
        foreach ($violations as $violation) {
            $output->writeln(sprintf("<error>%s</error>", $violation->getMessage()));
        }
        if (0 !== $violations->count()) {
            $output->writeln(sprintf('<error>Product "%s" is not valid</error>', $identifier));

            return -1;
        }

        if (null !== $productDraft = $this->getEntityWithValuesDraftBuilder()->build($entityWithValues, $username)) {
            $status = EntityWithValuesDraftInterface::READY === (int) $input->getArgument('draft_status') ?
                EntityWithValuesDraftInterface::CHANGE_TO_REVIEW :
                EntityWithValuesDraftInterface::CHANGE_DRAFT;
            $productDraft->setAllReviewStatuses($status);

            $this->saveDraft($productDraft);
            $output->writeln(sprintf('<info>Draft "%s" has been created</info>', $identifier));
        } else {
            $output->writeln(sprintf('<info>No draft has been created because do diff has been found</info>'));

            return -1;
        }

        return 0;
    }

    /**
     * @param EntityWithValuesDraftInterface $productDraft
     */
    protected function saveDraft(EntityWithValuesDraftInterface $productDraft)
    {
        $saver = $this->getContainer()->get('pimee_workflow.saver.product_draft');
        $saver->save($productDraft);
    }

    /**
     * @return EntityWithValuesDraftBuilderInterface
     */
    protected function getEntityWithValuesDraftBuilder()
    {
        return $this->getContainer()->get('pimee_workflow.product.builder.draft');
    }
}
