<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Symfony\Component\Yaml\Yaml;

/**
 * Write files in Yaml
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class YamlWriter extends FileWriter
{
    const INLINE_ARRAY_LEVEL = 8;

    /** @var string */
    protected $filePath = '/tmp/export_%datetime%.yml';

    /** @var string */
    protected $header;

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $data = call_user_func_array('array_merge', $items);
        if (null !== $this->header) {
            $data = [];
            $data[$this->header] = call_user_func_array('array_merge', $items);
        }

        $path = $this->getPath();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $yaml = Yaml::dump($data, self::INLINE_ARRAY_LEVEL);

        if (false === file_put_contents($path, $yaml)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        $this->incrementSummaryInfo($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $configuration = parent::getConfigurationFields();
        $configuration = $configuration + ['header' => ['header' => null]];

        return $configuration;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $header
     *
     * @return YamlWriter
     */
    public function setHeader($header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @param array $data
     */
    protected function incrementSummaryInfo(array $data)
    {
        if (null !== $this->header) {
            foreach ($data[$this->header] as $item) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        } else {
            foreach ($data as $item) {
                $this->stepExecution->incrementSummaryInfo('write');
            }
        }
    }
}
