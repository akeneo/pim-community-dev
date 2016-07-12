<?php

namespace Pim\Bundle\BaseConnectorBundle\EventListener;

use Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidItemWriterRegistry
{
    /** @var ArchiverInterface[] */
    protected $writers = [];

    /**
     * Register a writer
     *
     * @param ArchiverInterface $writer
     *
     * @throws \InvalidArgumentException
     */
    public function registerWriter(ArchiverInterface $writer)
    {
        if (array_key_exists($writer->getName(), $this->writers)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'There is already a registered writer named "%s": %s',
                    $writer->getName(),
                    get_class($this->writers[$writer->getName()])
                )
            );
        }

        $this->writers[$writer->getName()] = $writer;
    }

    /**
     * Get an invalid item writers that supports the given job execution
     *
     * @return ArchiverInterface[]
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Get an invalid item writer archiver by name
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return ArchiverInterface
     */
    public function getWriter($name)
    {
        if (!isset($this->writers[$name])) {
            throw new \InvalidArgumentException(
                sprintf('Writer or Archiver named "%s" is not registered', $name)
            );
        }

        return $this->writers[$name];
    }
}
