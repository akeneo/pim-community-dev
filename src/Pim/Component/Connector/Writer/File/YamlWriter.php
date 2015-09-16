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

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $path = $this->getPath();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $yaml = Yaml::dump($items, self::INLINE_ARRAY_LEVEL);

        if (false === file_put_contents($path, $yaml)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }

        foreach ($items as $item) {
            $this->stepExecution->incrementSummaryInfo('write');
        }
    }
}
