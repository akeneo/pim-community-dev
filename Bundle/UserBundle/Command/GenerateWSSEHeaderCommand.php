<?php

namespace Oro\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateWSSEHeaderCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:wsse:generate-header');
        $this->setDescription('Generate X-WSSE HTTP header for a given user');
        $this->setDefinition(
            array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
            )
        );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $user     = $this
            ->getContainer()
            ->get('oro_user.manager')
            ->findUserByUsername($username);

        if (null === $user->getApi() || null === $user->getApi()->getApiKey()) {
            throw new \InvalidArgumentException(sprintf('User "%s" does not yet have an API key generated', $username));
        }

        $created = date('c');

        // http://stackoverflow.com/questions/18117695/how-to-calculate-wsse-nonce
        $prefix         = gethostname();
        $nonce          = base64_encode(substr(md5(uniqid($prefix . '_', true)), 0, 16));
        $passwordDigest = base64_encode(sha1(base64_decode($nonce) . $created . $user->getApi()->getApiKey(), true));

        $output->writeln('<info>To use WSSE authentication add following headers to the request:</info>');
        $output->writeln('Authorization: WSSE profile="UsernameToken"');
        $output->writeln(
            sprintf(
                'X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $username,
                $passwordDigest,
                $nonce,
                $created
            )
        );
        $output->writeln('');

        return 0;
    }
}
