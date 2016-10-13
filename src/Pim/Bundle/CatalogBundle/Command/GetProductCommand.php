<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Get a json normalized product
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:get')
            ->setDescription('Get a json normalized product')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            )
            ->addArgument(
                'username',
                InputArgument::OPTIONAL,
                sprintf('The author of updated product (admin by default)'),
                'admin'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product = $this->getProduct($identifier);
        if (false === $product) {
            $output->writeln(sprintf('<error>product with identifier "%s" not found<error>', $identifier));

            return -1;
        }

        $username = $input->getArgument('username');
        if (!$this->createToken($output, $username)) {
            return;
        }

        $normalizedProduct = $this->getContainer()->get('pim_serializer')->normalize($product, 'standard', []);

        $output->write(json_encode($normalizedProduct));
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');
        $product = $repository->findOneByIdentifier($identifier);

        return $product;
    }

    /**
     * @return TokenStorageInterface
     */
    protected function getTokenStorage()
    {
        return $this->getContainer()->get('security.token_storage');
    }

    /**
     * @return \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    public function getSecurityFacade()
    {
        return $this->getContainer()->get('oro_security.security_facade');
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
        $userManager = $this->getContainer()->get('oro_user.manager');
        $user = $userManager->findUserByUsername($username);

        if (null === $user) {
            $output->writeln(sprintf('<error>Username "%s" is unknown<error>', $username));

            return false;
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->getTokenStorage()->setToken($token);

        return true;
    }
}
