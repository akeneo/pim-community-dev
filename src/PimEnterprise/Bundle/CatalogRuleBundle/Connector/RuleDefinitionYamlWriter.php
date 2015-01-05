<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Bundle\BaseConnectorBundle\Writer\File\FileWriter;
use Symfony\Component\Yaml\Yaml;

/**
 * Write rules definition in yaml
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleDefinitionYamlWriter extends FileWriter
{
    protected $filePath = '/tmp/rule_export_%datetime%.yml';

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $yaml = Yaml::dump($items[0], 8);

        $path = $this->getPath();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        if (false === file_put_contents($path, $yaml)) {
            throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
        }
    }
}
