<?php

namespace Oro\Bundle\ImportExportBundle\ItemConverter;

abstract class AbstractItemConverter implements ItemConverterInterface
{
    const HEADER_TEMPLATE = '%property%_%index%_%parameter%';

    /**
     * @var int
     */
    protected $index;

    /**
     * @param string $property
     * @param array $input
     * @return mixed
     */
    public function convertToArray($property, array $input)
    {
        if (!$property || !$input || empty($input[$property])) {
            return $input;
        }

        $this->index = 0;

        return $this->processConversion($property, $input);
    }

    /**
     * @param string $property
     * @param string $parameter
     * @param int $index
     * @return string
     */
    protected function getHeaderText($property, $parameter, $index)
    {
        return str_replace(
            array('%property%', '%parameter%', '%index%'),
            array($property, $parameter, $index),
            self::HEADER_TEMPLATE
        );
    }

    /**
     * @param string $property
     * @param array $input
     * @return array
     */
    abstract protected function processConversion($property, array $input);
}
