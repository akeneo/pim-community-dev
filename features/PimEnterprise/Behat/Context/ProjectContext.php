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
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectContext extends Context implements ContextInterface
{
    /**
     * @Then /^the project "([^"]*)" has the following properties:$/
     */
    public function projectHasProperties($label, TableNode $properties)
    {
        $project = $this->getContainer()
            ->get('activity_manager.repository.project')
            ->findOneBy(['label' => $label]);

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties->getRows() as $property) {
            list($propertyName, $expectedValue) = $property;

            switch ($propertyName) {
                case 'Channel':
                    $actualValue = $project->getChannel()->getCode();
                    break;
                case 'Locale':
                    $actualValue = $project->getLocale()->getCode();
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
}
