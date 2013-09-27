<?php

namespace Oro\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\MessageCatalogue;

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
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $em = $this->getContainer()->get('doctrine');
        $repository =$em->getRepository('Oro\Bundle\UserBundle\Entity\User');
        $user = $repository->findOneBy(array('username' => $username));

        if (null === $user->getApi() || null === $user->getApi()->getApiKey()) {
            throw new \InvalidArgumentException(sprintf('User "%s" does not yet have an API key generated', $username));
        }

        $created = new \DateTime();
        $created = $created->format(DATE_ATOM);

        // http://stackoverflow.com/questions/18117695/how-to-calculate-wsse-nonce
        $prefix = gethostname();
        $nonce = base64_encode( substr( md5( uniqid( $prefix.'_', true)), 0, 16));
        $passwordDigest = base64_encode(sha1(base64_decode($nonce).$created.$user->getApi()->getApiKey(), true));

        $output->writeln(
            sprintf(
                'X-WSSE:UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
                $username,
                $passwordDigest,
                $nonce,
                $created
            )
        );

        return 0;
    }
}
