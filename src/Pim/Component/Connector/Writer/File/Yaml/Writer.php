<?php

namespace Pim\Component\Connector\Writer\File\Yaml;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\RuntimeErrorException;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Writer\File\AbstractFileWriter;
use Symfony\Component\Yaml\Yaml;

/**
 * Write files in Yaml
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Writer extends AbstractFileWriter implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    const INLINE_ARRAY_LEVEL = 8;

    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var string */
    protected $header;

    /**
     * @param ArrayConverterInterface $arrayConverter
     * @param string                  $header
     */
    public function __construct(ArrayConverterInterface $arrayConverter, $header = null)
    {
        parent::__construct();

        $this->arrayConverter = $arrayConverter;
        $this->header = $header;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $flatItems = [];
        foreach ($items as $item) {
            $flatItems[] = $this->arrayConverter->convert($item);
        }

        $flatItems = call_user_func_array('array_merge', $flatItems);
        if (null !== $this->header) {
            $data = [];
            $data[$this->header] = $flatItems;
        }

        $path = $this->getPath();
        if (!is_dir(dirname($path))) {
            $this->localFs->mkdir(dirname($path));
        }

        $yaml = Yaml::dump($data, self::INLINE_ARRAY_LEVEL);

        if (false === file_put_contents($path, $yaml, FILE_APPEND)) {
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
