<?php

namespace Oro\Bundle\FormBundle\Autocomplete;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

class FullNameSearchHandler extends SearchHandler
{
    /**
     * @var NameFormatter
     */
    protected $nameFormatter;

    /**
     * @param NameFormatter $nameFormatter
     */
    public function setNameFormatter(NameFormatter $nameFormatter)
    {
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function convertItem($item)
    {
        $result = parent::convertItem($item);
        $result['fullName'] = $this->getFullName($item);

        return $result;
    }

    /**
     * Apply name formatter to get entity's full name
     *
     * @param mixed $entity
     * @return string
     * @throws \RuntimeException
     */
    protected function getFullName($entity)
    {
        if (!$this->nameFormatter) {
            throw new \RuntimeException('Name formatter must be configured');
        }
        return $this->nameFormatter->format($entity);
    }

    /**
     * Gets key of full name property in result item
     *
     * @return string
     */
    protected function getFullNameKey()
    {
        return 'fullName';
    }
}
