<?php

declare(strict_types=1);

namespace Pim\Behat\Extension\PimFormatter\Output\Node\Printer;

use Behat\Behat\Output\Node\EventListener\JUnit\JUnitOutlineStoreListener;
use Behat\Behat\Output\Node\Printer\Helper\ResultToStringConverter;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioLikeInterface;
use Behat\Testwork\Output\Formatter;
use Behat\Testwork\Tester\Result\TestResult;

/**
 * Scenario printer of the PIM, for the CI. Instead of displaying the namespace of the scenario,
 * it displays the filepath of the scenario.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @see Behat\Behat\Output\Node\Printer\JUnit\JUnitScenarioPrinter
 */
final class PimScenarioPrinter
{
    /** @var string */
    private $basePath;

    /** @var ResultToStringConverter */
    private $resultConverter;

    /** @var JUnitOutlineStoreListener */
    private $outlineStoreListener;

    /**
     * @param string                    $basePath
     * @param ResultToStringConverter   $resultConverter
     * @param JUnitOutlineStoreListener $outlineListener
     */
    public function __construct(string $basePath, ResultToStringConverter $resultConverter, JUnitOutlineStoreListener $outlineListener)
    {
        $this->basePath = $basePath;
        $this->resultConverter = $resultConverter;
        $this->outlineStoreListener = $outlineListener;
    }

    /**
     * @param Formatter             $formatter
     * @param FeatureNode           $feature
     * @param ScenarioLikeInterface $scenario
     * @param TestResult            $result
     */
    public function printOpenTag(Formatter $formatter, FeatureNode $feature, ScenarioLikeInterface $scenario, TestResult $result) : void
    {
        $fileAndLine = sprintf('%s:%s', $this->relativizePaths($feature->getFile()), $scenario->getLine());

        $outputPrinter = $formatter->getOutputPrinter();

        $outputPrinter->addTestcase([
            'name'   => $fileAndLine,
            'status' => $this->resultConverter->convertResultToString($result),
        ]);
    }

    /**
     * Transforms path to relative.
     *
     * @param string $path
     *
     * @return string
     */
    private function relativizePaths($path)
    {
        if (!$this->basePath) {
            return $path;
        }

        return str_replace($this->basePath . DIRECTORY_SEPARATOR, '', $path);
    }
}
