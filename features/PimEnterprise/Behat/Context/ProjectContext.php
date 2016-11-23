<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Behat\Context;

use Akeneo\ActivityManager\Behat\Context;
use Akeneo\ActivityManager\Behat\ContextInterface;
use Akeneo\ActivityManager\Component\Model\DatagridViewTypes;
use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Webmozart\Assert\Assert;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectContext extends Context implements ContextInterface
{
    /**
     * @Then /^the project "([^"]*)" for channel "([^"]*)" and locale "([^"]*)" has the following properties:$/
     */
    public function projectHasProperties($label, $channelCode, $localeCode, TableNode $properties)
    {
        $project = $this->findProjectByLabelChannelLocale($label, $channelCode, $localeCode);

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
                case 'Products':
                    $productIdentifiers = explode(',', $expectedValue);
                    $actualValue = $project->getProducts()->count();
                    $expectedValue = count($productIdentifiers);
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

        Assert::notNull($datagridView, 'The project %s does not have a datagrid view');
        Assert::eq(
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
     * @param string $label
     * @param string $channelCode
     * @param string $localeCode
     *
     * @return ProjectInterface
     *
     * @throws \UnexpectedValueException
     */
    private function findProjectByLabelChannelLocale($label, $channelCode, $localeCode)
    {
        $channel = $this->getContainer()
            ->get('pim_catalog.repository.channel')
            ->findOneByIdentifier($channelCode);

        $locale = $this->getContainer()
            ->get('pim_catalog.repository.locale')
            ->findOneByIdentifier($localeCode);

        $project = $this->getContainer()
            ->get('activity_manager.repository.project')
            ->findOneBy([
                'label' => $label,
                'channel' => $channel,
                'locale' => $locale,
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
