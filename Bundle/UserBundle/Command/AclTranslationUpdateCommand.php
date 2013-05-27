<?php

namespace Oro\Bundle\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Translation\MessageCatalogue;

class AclTranslationUpdateCommand extends ContainerAwareCommand
{
    /**
     * Console command configuration
     */
    public function configure()
    {
        $this->setName('oro:acl:translation-update');
        $this->setDescription('Load ACL translations');
        $this->setDefinition(
            array(
                 new InputArgument('locale', InputArgument::REQUIRED, 'The locale'),
                 new InputArgument('bundle', InputArgument::REQUIRED, 'The bundle where to load the messages'),
                 new InputOption(
                     'prefix', null, InputOption::VALUE_OPTIONAL,
                     'Override the default prefix', '__'
                 ),
                 new InputOption(
                     'output-format', null, InputOption::VALUE_OPTIONAL,
                     'Override the default output format', 'yml'
                 ),
                 new InputOption(
                     'dump-messages', null, InputOption::VALUE_NONE,
                     'Should the messages be dumped in the console'
                 ),
                 new InputOption(
                     'force', null, InputOption::VALUE_NONE,
                     'Should the update be done'
                 )
            )
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $writer = $this->getContainer()->get('translation.writer');
        if (!$this->checkParameters($input, $output, $writer)) {
            return 1;
        }

        // get bundle directory
        $foundBundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('bundle'));
        $bundleTransPath = $foundBundle->getPath() . '/Resources/translations';

        $output->writeln(
            sprintf(
                'Generating "<info>%s</info>" translation files for "<info>%s</info>"',
                $input->getArgument('locale'),
                $foundBundle->getName()
            )
        );
        // create catalogue
        $catalogue = new MessageCatalogue($input->getArgument('locale'));
        $output->writeln('Parsing templates');
        $this->getContainer()->get('oro_user.acl_manager')->parseAclTokens(
            $foundBundle->getPath(),
            $catalogue,
            $input->getOption('prefix')
        );

        // load any existing messages from the translation files
        $output->writeln('Loading translation files');
        $loader = $this->getContainer()->get('translation.loader');
        $loader->loadMessages($bundleTransPath, $catalogue);

        // show compiled list of messages
        if ($input->getOption('dump-messages') === true) {
            $this->dumpMessages($input, $output, $catalogue);
        }

        // save the files
        if ($input->getOption('force') === true) {
            $this->saveMessages($input, $output, $catalogue, $writer, $bundleTransPath);
        }
    }

    /**
     * Save massage files
     *
     * @param $input
     * @param $output
     * @param $catalogue
     * @param $writer
     * @param $bundleTransPath
     */
    protected function saveMessages($input, $output, $catalogue, $writer, $bundleTransPath)
    {
        $output->writeln('Writing files');
        $writer->writeTranslations($catalogue, $input->getOption('output-format'), array('path' => $bundleTransPath));
    }

    /**
     * Dump messages to the  output
     *
     * @param $input
     * @param $output
     * @param $catalogue
     */
    protected function dumpMessages($input, $output, $catalogue)
    {
        foreach ($catalogue->getDomains() as $domain) {
            $output->writeln(sprintf("\nDisplaying messages for domain <info>%s</info>:\n", $domain));
            $output->writeln(Yaml::dump($catalogue->all($domain), 10));
        }
        if ($input->getOption('output-format') == 'xliff') {
            $output->writeln('Xliff output version is <info>1.2</info>');
        }
    }

    /**
     * Check command parameters
     *
     * @param $input
     * @param $output
     * @param $writer
     *
     * @return bool
     */
    protected function checkParameters($input, $output, $writer)
    {
        // check presence of force or dump-message
        if ($input->getOption('force') !== true && $input->getOption('dump-messages') !== true) {
            $output->writeln('<info>You must choose one of --force or --dump-messages</info>');

            return false;
        }

        // check format
        $supportedFormats = $writer->getFormats();
        if (!in_array($input->getOption('output-format'), $supportedFormats)) {
            $output->writeln('<error>Wrong output format</error>');
            $output->writeln('Supported formats are ' . implode(', ', $supportedFormats) . '.');

            return false;
        }

        return true;
    }
}
