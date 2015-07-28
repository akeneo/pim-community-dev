<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Sends a draft for approval
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class SendDraftForApprovalCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:draft:send-for-approval')
            ->setDescription('Sends a product draft for approval')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                sprintf('The author of the draft')
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product = $this->getProduct($identifier);
        if (null === $product) {
            $output->writeln(sprintf('<error>Product with identifier "%s" not found</error>', $identifier));

            return -1;
        }

        $username = $input->getArgument('username');
        if (!$this->createToken($output, $username)) {
            return -1;
        }

        $productDraft = $this->getProductDraft($product, $username);
        if (!$productDraft) {
            $output->writeln(sprintf('<error>Product draft "%s" not found</error>', $identifier));

            return -1;
        }

        $productDraft->setStatus(ProductDraftInterface::READY);
        $this->saveDraft($productDraft);

        return 0;
    }

    /**
     * @param ProductDraftInterface $productDraft
     */
    protected function saveDraft(ProductDraftInterface $productDraft)
    {
        $saver = $this->getContainer()->get('pimee_workflow.saver.product_draft');
        $saver->save($productDraft);
    }

    /**
     * @param ProductInterface $product
     * @param string           $username
     *
     * @return null|ProductDraftInterface
     */
    protected function getProductDraft(ProductInterface $product, $username)
    {
        $repository = $this->getContainer()->get('pimee_workflow.repository.product_draft');

        return $repository->findUserProductDraft($product, $username);
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product    = $repository->findOneByIdentifier($identifier);

        return $product;
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContextInterface;
     */
    protected function getSecurityContext()
    {
        return $this->getContainer()->get('security.context');
    }

    /**
     * Create a security token from the given username
     *
     * @param OutputInterface $output
     * @param string          $username
     *
     * @return bool
     */
    protected function createToken(OutputInterface $output, $username)
    {
        $userRepository = $this->getContainer()->get('pim_user.repository.user');
        $user = $userRepository->findOneByIdentifier($username);

        if (null === $user) {
            $output->writeln(sprintf('<error>Username "%s" is unknown<error>', $username));

            return false;
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getSecurityContext()->setToken($token);

        return true;
    }
}
