<?php

namespace Oro\Bundle\EmailBundle\Provider;

use Oro\Bundle\EmailBundle\Entity\EmailOrigin;

class EmailBodyLoaderSelector
{
    /**
     * @var EmailBodyLoaderInterface[]
     */
    private $loaders = array();

    /**
     * Adds implementation of EmailBodyLoaderInterface
     *
     * @param EmailBodyLoaderInterface $loader
     */
    public function addLoader(EmailBodyLoaderInterface $loader)
    {
        $this->loaders[] = $loader;
    }

    /**
     * Gets implementation of EmailBodyLoaderInterface for the given email origin
     *
     * @param EmailOrigin $origin
     * @return EmailBodyLoaderInterface
     * @throws \RuntimeException
     */
    public function select(EmailOrigin $origin)
    {
        foreach ($this->loaders as $loader) {
            if ($loader->supports($origin)) {
                return $loader;
            }
        }

        throw new \RuntimeException(sprintf('Cannot find an email body loader. Origin id: %d.', $origin->getId()));
    }
}
