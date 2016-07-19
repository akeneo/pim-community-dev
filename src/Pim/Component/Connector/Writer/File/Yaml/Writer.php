<?php

namespace Pim\Component\Connector\Writer\File\Yaml;

use Akeneo\Component\Batch\Job\RuntimeErrorException;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Symfony\Component\Yaml\Yaml;

/**
 * Write files in Yaml
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Writer extends AbstractFileWriter
{
    const INLINE_ARRAY_LEVEL = 8;

    /** @var string */
    protected $header;

    /**
     * @param string $header
     */
    public function __construct($header = null)
    {
        parent::__construct();

        $this->header = $header;
    }

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
            $this->localFs->mkdir(dirname($path));
        }

        $yaml = Yaml::dump($data, self::INLINE_ARRAY_LEVEL);

        if (false === file_put_contents($path, $yaml)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        $this->incrementSummaryInfo($data);
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
