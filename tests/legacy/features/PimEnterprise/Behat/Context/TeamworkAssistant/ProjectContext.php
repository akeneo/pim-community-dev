<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Behat\Context\TeamworkAssistant;

use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Datagrid\DatagridViewTypes;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Then /^the project "([^"]*)" for channel "([^"]*)" and locale "([^"]*)" has the following properties:$/
     */
    public function projectHasProperties($label, $channelCode, $localeCode, TableNode $properties)
    {
        $project = $this->spin(function () use ($label, $channelCode, $localeCode) {
            return $this->findProjectByLabelChannelLocale($label, $channelCode, $localeCode);
        }, sprintf('Cannot find the project %s', $label));

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties->getRows() as $property) {
            list($propertyName, $expectedValue) = $property;
            switch ($propertyName) {
                case 'Due date':
                    $actualValue = $accessor->getValue($project, strtolower(str_replace(' ', '_', $propertyName)));
                    $actualValue = $actualValue->format('Y-m-d');
                    break;
                case 'Channel':
                    $actualValue = $project->getChannel()->getCode();
                    break;
                case 'Locale':
                    $actualValue = $project->getLocale()->getCode();
                    break;
                case 'Owner':
                    $actualValue = $project->getOwner()->getUsername();
                    break;
                default:
                    $actualValue = $accessor->getValue($project, $propertyName);
                    break;
            }

            if ($expectedValue !== $actualValue) {
                throw new \DomainException(
                    sprintf(
                        'Given value does not match the expected value, "%s" expected, "%s" given, property: "%s"',
                        $expectedValue,
                        $actualValue,
                        $propertyName
                    )
                );
            }
        }
    }

    /**
     * @Given /^the project "([^"]*)" for channel "([^"]*)" and locale "([^"]*)" has a project datagrid view$/
     */
    public function projectHasDatagridView($label, $channelCode, $localeCode)
    {
        $project = $this->findProjectByLabelChannelLocale($label, $channelCode, $localeCode);
        $datagridView = $project->getDatagridView();
        $type = $datagridView->getType();

        Assert::assertNotNull($datagridView, 'The project %s does not have a datagrid view');
        Assert::assertEquals(
            DatagridViewTypes::PROJECT_VIEW,
            $type,
            sprintf(
                'the project datagrid view has the right type, %s given, %s expected %s',
                $label,
                $type,
                DatagridViewTypes::PROJECT_VIEW
            )
        );
    }

    /**
     * @Given /^the following projects:$/
     */
    public function theFollowingProjects(TableNode $table)
    {
        $factory = $this->getService('pimee_teamwork_assistant.factory.project');

        $projects = [];
        foreach ($table->getHash() as $field => $data) {
            if (!isset($data['datagrid_view'])) {
                $data['datagrid_view']['columns'] = 'sku,enable';
                $data['datagrid_view']['filters'] = '/filters?key=value';
            }

            $data['product_filters'] = json_decode($data['product_filters'], true);

            $projects[] = $factory->create($data);
        }
        $this->getService('pimee_teamwork_assistant.saver.project')->saveAll($projects);

        foreach ($projects as $project) {
            $this->generateProject($project->getCode());
        }
    }

    /**
     * @Given /^I run computation of the project "([^"]*)"$/
     */
    public function iComputeTheProject($projectCode)
    {
        $this->generateProject($projectCode);
    }

    /**
     * Launch the project calculation job for the given project
     *
     * @param string $projectCode
     *
     * @throws \Exception
     */
    private function generateProject($projectCode)
    {
        $pathFinder = new PhpExecutableFinder();

        exec(
            sprintf(
                '%s %s/console akeneo:batch:job project_calculation --env=behat -c {\"project_code\":\"%s\"}',
                $pathFinder->find(),
                $this->getMainContext()->getContainer()->getParameter('kernel.root_dir'),
                $projectCode
            )
        );
    }

    /**
     * @param string $label
     * @param string $channelCode
     * @param string $localeCode
     *
     * @throws \UnexpectedValueException
     * @return ProjectInterface
     *
     */
    private function findProjectByLabelChannelLocale($label, $channelCode, $localeCode)
    {
        $channel = $this->getService('pim_catalog.repository.channel')
            ->findOneByIdentifier($channelCode);

        $locale = $this->getService('pim_catalog.repository.locale')
            ->findOneByIdentifier($localeCode);

        $project = $this->getService('pimee_teamwork_assistant.repository.project')
            ->findOneBy([
                'label'   => $label,
                'channel' => $channel,
                'locale'  => $locale,
            ]);

        if (null === $project) {
            throw new \UnexpectedValueException(
                sprintf(
                    'The project "%s" does not exist for channel "%s" and locale "%s"',
                    $label,
                    $channelCode,
                    $localeCode
                )
            );
        }

        return $project;
    }
}
