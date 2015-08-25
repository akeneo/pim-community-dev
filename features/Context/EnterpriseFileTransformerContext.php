<?php

namespace Context;

use Akeneo\Component\FileTransformer\Transformation\TransformationInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Process\ExecutableFinder;

/**
 * A context for testing file transformations
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseFileTransformerContext extends RawMinkContext
{
    const DEFAULT_PERCEPTUAL_DIFF = '/usr/bin/perceptualdiff';

    /** @var \SplFileInfo */
    protected $imageFile;

    /** @var array */
    protected $placeholderValues = [];

    /**
     * @BeforeScenario
     */
    public function resetPlaceholderValues()
    {
        $this->placeholderValues = [
            '%tmp%'      => getenv('BEHAT_TMPDIR') ?: '/tmp/pim-behat',
            '%fixtures%' => __DIR__ . '/fixtures',
        ];
    }

    /**
     * @BeforeScenario
     */
    public function resetImageFile()
    {
        $this->imageFile = null;
    }

    /**
     * @Given /^I apply the following transformations on the input file "([^"]*)"$/
     */
    public function iApplyTheFollowingTransformationOnTheInputFile($inputPathname, TableNode $table)
    {
        $inputPathname = $this->replacePlaceholders($inputPathname);
        $file = $this->copyInputFile($inputPathname);

        foreach ($table->getHash() as $row) {
            $transformation = $this->getTransformation($row['type']);
            $options = json_decode($row['options'], true);
            $transformation->transform($file, $options);
        }

        $this->imageFile = $file;
    }


    /**
     * @Then /^the result file should be the same than "([^"]*)"$/
     */
    public function theResultFileShouldBeTheSameThan($inputPathname)
    {
        if (null === $this->imageFile) {
            throw new \Exception('No image file has been transformed.');
        }

        $inputPathname = $this->replacePlaceholders($inputPathname);

        $this->execPerceptualDiff($inputPathname, $this->imageFile->getPathname());
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function replacePlaceholders($value)
    {
        return strtr($value, $this->placeholderValues);
    }

    /**
     * @param $type
     *
     * @return TransformationInterface
     */
    protected function getTransformation($type)
    {
        $container = $this->getMainContext()->getContainer();

        return $container->get('akeneo_file_transformer.transformation.image.' . $type);
    }

    /**
     * @param $inputPathname
     *
     * @return \SplFileInfo
     * @throws \Exception
     */
    protected function copyInputFile($inputPathname)
    {
        $inputPathname = realpath($inputPathname);
        $outputPathname = $this->replacePlaceholders('%tmp%' . DIRECTORY_SEPARATOR . uniqid());
        mkdir(dirname($outputPathname));

        if (false === copy($inputPathname, $outputPathname)) {
            throw new \Exception(sprintf('Impossible to copy the file "%s" to "%s"', $inputPathname, $outputPathname));
        }

        return new \SplFileInfo($outputPathname);
    }

    /**
     * @param string $expectedPathname
     * @param string $pathname
     */
    protected function execPerceptualDiff($expectedPathname, $pathname)
    {
        $executableFinder = new ExecutableFinder();
        $perceptualDiff = $executableFinder->find('perceptualdiff', self::DEFAULT_PERCEPTUAL_DIFF);

        $cmd = sprintf(
            '%s %s %s',
            $perceptualDiff,
            $expectedPathname,
            $pathname
        );

        $output = [];
        $status = null;

        exec($cmd, $output, $status);

        if (0 !== $status) {
            throw new \LogicException(print_r($output, true));
        }
    }
}
