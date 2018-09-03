<?php

namespace Akeneo\Tool\Component\Connector\Writer\File\Yaml;

use Akeneo\Tool\Component\Batch\Item\FlushableInterface;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Job\RuntimeErrorException;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractFileWriter;
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
    StepExecutionAwareInterface,
    FlushableInterface
{
    const INLINE_ARRAY_LEVEL = 8;
    const INDENT_SPACES = 4;

    /** @var ArrayConverterInterface */
    protected $arrayConverter;

    /** @var string */
    protected $header;

    /** @var bool */
    protected $isFirstWriting;

    /**
     * @param ArrayConverterInterface $arrayConverter
     * @param string                  $header
     */
    public function __construct(ArrayConverterInterface $arrayConverter, $header = null)
    {
        parent::__construct();

        $this->arrayConverter = $arrayConverter;
        $this->header = $header;
        $this->isFirstWriting = true;
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

        $path = $this->getPath();
        if (!is_dir(dirname($path))) {
            $this->localFs->mkdir(dirname($path));
        }

        if ($this->isFirstWriting) {
            $items = $this->overwrite($flatItems, $path);
            $this->isFirstWriting = false;
            $this->incrementSummaryInfo($items);

            return;
        }

        $items = $this->append($flatItems, $path);
        $this->incrementSummaryInfo($items);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->isFirstWriting = true;
    }

    /**
     * @param array $items
     * @param string $path
     *
     * @return array
     */
    protected function overwrite(array $items, string $path): array
    {
        $data = [];

        if (null !== $this->header) {
            $data[$this->header] = $items;
        }

        $yaml = Yaml::dump($data, self::INLINE_ARRAY_LEVEL, self::INDENT_SPACES);

        if (false === file_put_contents($path, $yaml)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        return $data;
    }

    /**
     * @param array $items
     * @param string $path
     *
     * @return array
     */
    protected function append(array $items, string $path): array
    {
        $yaml = Yaml::dump($items, self::INLINE_ARRAY_LEVEL, self::INDENT_SPACES);

        if (null !== $this->header) {
            $yaml = preg_replace('/^/m', '    ', $yaml);
            $items = [$this->header => $items];
        }

        if (false === file_put_contents($path, $yaml, FILE_APPEND)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        return $items;
    }

    /**
     * @param array $data
     */
    protected function incrementSummaryInfo(array $data): void
    {
        $items = null !== $this->header ? $data[$this->header] : $data;

        foreach ($items as $item) {
            $this->stepExecution->incrementSummaryInfo('write');
        }
    }
}
