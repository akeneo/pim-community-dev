<?php

namespace PimEnterprise\Behat\Context\TeamworkAssistant;

use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Behat\ChainedStepsExtension\Step\Given;
use Behat\ChainedStepsExtension\Step\Then;
use Behat\ChainedStepsExtension\Step\When;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Context\PimContext;
use PimEnterprise\Behat\Decorator\Widget\TeamworkAssistantWidgetDecorator;

class WidgetContext extends PimContext
{
    use SpinCapableTrait;

    private $isProjectOwned = false;
    /**
     * @Then /^I should not see the (project) selector$/
     * @Then /^I should not see the (contributor) selector$/
     */
    public function iShouldNotSeeTheSelector($selector)
    {
        $getSelectorMethod = sprintf('get%sSelector', ucfirst($selector));
        try {
            $this->getTeamworkAssistantWidget()->$getSelectorMethod();
            throw new ExpectationException(
                sprintf('%s selector is visible but must not.', $selector),
                $this->getSession()
            );
        } catch (TimeoutException $e) {
            return true;
        }
    }
    /**
     * @Then /^I should see the (project) selector$/
     * @Then /^I should see the (contributor) selector$/
     */
    public function iShouldSeeTheSelector($selector)
    {
        $getSelectorMethod = sprintf('get%sSelector', ucfirst($selector));

        $this->getTeamworkAssistantWidget()->$getSelectorMethod();
    }

    /**
     * @Then /^I should see the teamwork assistant widget$/
     */
    public function iShouldSeeTheTeamworkAssistantWidget()
    {
        $this->getTeamworkAssistantWidget();
    }

    /**
     * @When /^I select "([^"]*)" project$/
     *
     * @param string $projectLabel
     */
    public function iSelectProject($projectLabel)
    {
        $this->getTeamworkAssistantWidget()->selectProject($projectLabel);
    }

    /**
     * @When /^I select "([^"]*)" contributor$/
     *
     * @param string $contributorName
     */
    public function iSelectContributor($contributorName)
    {
        $this->getTeamworkAssistantWidget()->selectContributor($contributorName);
    }

    /**
     * @Then /^I should see the following teamwork assistant completeness:$/
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingTeamworkAssistantCompleteness(TableNode $table)
    {
        $completeness = $this->getTeamworkAssistantWidget()->getCompleteness();
        foreach ($table->getHash() as $expectedData) {
            foreach ($expectedData as $field => $expectedValue) {
                if ($completeness[$field] !== $expectedValue) {
                    throw new ExpectationException(
                        sprintf(
                            'Expected "%s:%s" for completeness and "%s" found.',
                            $field,
                            $expectedValue,
                            $completeness[$field]
                        ),
                        $this->getSession()
                    );
                }
            }
        }
    }

    /**
     * @Then /^I should( not)? see the "([^"]*)" project in the widget$/
     *
     * @param string $not
     * @param string $projectName
     *
     * @throws \UnexpectedValueException
     */
    public function iShouldSeeTheProject($not, $projectName)
    {
        $values = $this->getTeamworkAssistantWidget()->getChoicesFromProjectsSelector();
        $found = false;

        foreach ($values as $value) {
            if (strpos($value, $projectName) !== false) {
                $found = true;
            }
        }

        if ($not && $found) {
            throw new \UnexpectedValueException(
                sprintf('Project "%s" should not be displayed.', $projectName)
            );
        } elseif (!$not && !$found) {
            throw new \UnexpectedValueException(
                sprintf('Project "%s" should be displayed.', $projectName)
            );
        }
    }

    /**
     * @Then /^I should not see the select project link in the "([^"]*)" section of the teamwork assistant widget$/
     *
     * @param string $sectionName
     */
    public function iShouldNotSeeTheSelectProjectLinkInTheSection($sectionName)
    {
        $this->spin(function () use ($sectionName) {
            $link = $this->getTeamworkAssistantWidget()->getLinkFromSection($sectionName);

            return null !== $link ? false : true;
        }, sprintf('The "%s" section in the widget should not be clickable.', $sectionName));
    }

    /**
     * @When /^I click on the "([^"]*)" section of the teamwork assistant widget$/
     *
     * @param string $sectionName
     */
    public function iClickOnTheSectionOfTheTeamworkAssistantWidget($sectionName)
    {
        $link = $this->spin(function () use ($sectionName) {
            return $this->getTeamworkAssistantWidget()->getLinkFromSection($sectionName);
        }, sprintf('The "%s" section in the widget should be clickable.', $sectionName));

        $link->click();
    }

    /**
     * Get the decorated teamwork assistant widget
     *
     * @return TeamworkAssistantWidgetDecorator
     */
    protected function getTeamworkAssistantWidget()
    {
        return $this->getCurrentPage()->getTeamworkAssistantWidget();
    }

    /**
     * @Given /^a project not owned by Julia (.*)$/
     */
    public function aProjectNotOwnedByJuliaWithProductsToDo()
    {
        $this->createProject('Mary');
    }

    /**
     * @When /^Julia wants to display her products to do from the teamwork assistant widget$/
     */
    public function juliaWantsToDisplayHerProductsToDoFromTheTeamworkAssistantWidget()
    {
        if ($this->isProjectOwned) {
            return [
                new Given('I am logged in as "Julia"'),
                new Given('I am on the dashboard page'),
                new Given('I select "Julia" contributor'),
                new Given('I should see the text "2016 summer collection"'),
                new Given('I should see the text "Julia Stark"'),
                new When('I click on the "todo" section of the teamwork assistant widget')
            ];
        }

        return [
            new Given('I am logged in as "Julia"'),
            new Given('I am on the dashboard page'),
            new When('I click on the "todo" section of the teamwork assistant widget')
        ];
    }

    /**
     * @Then /^the list of products to do by Julia is displayed$/
     */
    public function theListOfProductsToDoByJuliaIsDisplayed()
    {
        return [
            new Then('I should be on the products page'),
            new Then('the criteria of "project_completeness" filter should be "Todo"'),
        ];
    }

    /**
     * @When /^Julia wants to display her products in progress from the teamwork assistant widget$/
     */
    public function juliaWantsToDisplayHerProductsInProgressFromTheTeamworkAssistantWidget()
    {
        if ($this->isProjectOwned) {
            return [
                new Given('I am logged in as "Julia"'),
                new Given('I am on the dashboard page'),
                new Given('I select "Julia" contributor'),
                new Given('I should see the text "2016 summer collection"'),
                new Given('I should see the text "Julia Stark"'),
                new When('I click on the "in-progress" section of the teamwork assistant widget')
            ];
        }

        return [
            new Given('I am logged in as "Julia"'),
            new Given('I am on the dashboard page'),
            new When('I click on the "in-progress" section of the teamwork assistant widget')
        ];
    }

    /**
     * @Then /^the list of products in progress by Julia is displayed$/
     */
    public function theListOfProductsInProgressByJuliaIsDisplayed()
    {
        return [
            new Then('I should be on the products page'),
            new Then('the criteria of "project_completeness" filter should be "In progress"'),
        ];
    }

    /**
     * @When /^Julia wants to display her products done from the teamwork assistant widget$/
     */
    public function juliaWantsToDisplayHerProductsDoneFromTheTeamworkAssistantWidget()
    {
        if ($this->isProjectOwned) {
            return [
                new Given('I am logged in as "Julia"'),
                new Given('I am on the dashboard page'),
                new Given('I select "Julia" contributor'),
                new Given('I should see the text "2016 summer collection"'),
                new Given('I should see the text "Julia Stark"'),
                new When('I click on the "done" section of the teamwork assistant widget')
            ];
        }

        return [
            new Given('I am logged in as "Julia"'),
            new Given('I am on the dashboard page'),
            new When('I click on the "done" section of the teamwork assistant widget')
        ];
    }

    /**
     * @Then /^the list of products done by Julia is displayed$/
     */
    public function theListOfProductsDoneByJuliaIsDisplayed()
    {
        return [
            new Then('I should be on the products page'),
            new Then('the criteria of "project_completeness" filter should be "Done"'),
        ];
    }

    /**
     * @Given /^a project owned by Julia with products (.*)$/
     */
    public function aProjectOwnedByJuliaWithProductsToDoByAllContributors()
    {
        $this->createProject('Julia');
        $this->isProjectOwned = true;
    }

    /**
     * @When /^Julia wants to display the products to do by all contributors of the project from the teamwork assistant widget$/
     */
    public function juliaWantsToDisplayTheProductsToDoByAllContributorsOfTheProjectFromTheTeamworkAssistantWidget()
    {
        return [
            new Given('I am logged in as "Julia"'),
            new Given('I am on the dashboard page'),
            new When('I click on the "todo" section of the teamwork assistant widget')
        ];
    }

    /**
     * @Then /^the list of products to do by all contributors of the project is displayed$/
     */
    public function theListOfProductsToDoByAllContributorsOfTheProjectIsDisplayed()
    {
        return [
            new Then('I should be on the products page'),
            new Then('the criteria of "project_completeness" filter should be "Todo (project overview)"'),
        ];
    }

    /**
     * @When /^Julia wants to display the products in progress for all contributors of the project from the teamwork assistant widget$/
     */
    public function juliaWantsToDisplayTheProductsInProgressForAllContributorsOfTheProjectFromTheTeamworkAssistantWidget()
    {
        return [
            new Given('I am logged in as "Julia"'),
            new Given('I am on the dashboard page'),
            new When('I click on the "in-progress" section of the teamwork assistant widget')
        ];
    }

    /**
     * @Then /^the list of products in progress for all contributors of the project is displayed$/
     */
    public function theListOfProductsInProgressForAllContributorsOfTheProjectIsDisplayed()
    {
        return [
            new Then('I should be on the products page'),
            new Then('the criteria of "project_completeness" filter should be "In progress (project overview)"'),
        ];
    }

    /**
     * @When /^Julia wants to display the products in progress from the teamwork assistant widget for all contributors$/
     */
    public function juliaWantsToDisplayTheProductsInProgressFromTheTeamworkAssistantWidgetForAllContributors()
    {
        return [
            new Given('I am logged in as "Julia"'),
            new Given('I am on the dashboard page'),
            new When('I click on the "done" section of the teamwork assistant widget')
        ];
    }

    /**
     * @Then /^the list of products in progress in the project for all contributors is displayed$/
     */
    public function theListOfProductsInProgressInTheProjectForAllContributorsIsDisplayed()
    {
        return [
            new Then('I should be on the products page'),
            new Then('the criteria of "project_completeness" filter should be "Done (project overview)"'),
        ];
    }

    private function createProject(string $user): void
    {
        $projectData = [
            'label' => '2016 summer collection',
            'description' => '2016 summer collection',
            'due_date' => '2118-12-13',
            'datagrid_view' => [
                'filters' => 'i=1&p=25&s%5Bupdated%5D=1&f%5Bscope%5D%5Bvalue%5D=ecommerce&f%5Bweight%5D%5Bvalue%5D=6&f%5Bweight%5D%5Btype%5D=5&f%5Bweight%5D%5Bunit%5D=OUNCE&f%5Bcategory%5D%5Bvalue%5D%5BtreeId%5D=1003&f%5Bcategory%5D%5Bvalue%5D%5BcategoryId%5D=1004&f%5Bcategory%5D%5Btype%5D=1&t=product-grid',
                'columns' => 'identifier,image,label,family,enabled,completeness,created,updated,complete_variant_products,success,[object Object]'
            ],
            'locale' => 'en_US',
            'owner' => $user,
            'channel' => 'ecommerce',
            'product_filters' => [
                [
                    'field'    => 'categories',
                    'operator' => 'IN OR UNCLASSIFIED',
                    'value'    => ['default', 'clothing', 'high_tech', 'decoration'],
                    'context'  => [
                        'locale'        => 'en_US',
                        'scope'         => 'ecommerce',
                        'limit'         => 25,
                        'from'          => 0,
                        'type_checking' => false
                    ],
                    'type'     => 'field'
                ],
                [
                    'field'    => 'weight',
                    'operator' => '<',
                    'value'    => [
                        'unit'   => 'OUNCE',
                        'amount' => 6
                    ],
                    'context'  => [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'limit'  => 25,
                        'from'   => 0,
                        'field'  => 'weight'
                    ],
                    'type'     => 'attribute'
                ],
                [
                    'field'    => 'categories',
                    'operator' => 'IN CHILDREN',
                    'value'    => ['clothing'],
                    'context'  => [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'limit'  => 25,
                        'from'   => 0
                    ],
                    'type'     => 'field'
                ]
            ]
        ];


        $project = $this->getService('pimee_teamwork_assistant.factory.project')->create($projectData);
        $violation = $this->getService('validator')->validate($project);

        if (0 < count($violation)) {
            throw new \Exception('Project object is invalid');
        }

        $this->getService('pimee_teamwork_assistant.saver.project')->save($project);

        $this->calculateProject($project);
    }

    private function calculateProject(ProjectInterface $project): void
    {
        $this->getService('pimee_teamwork_assistant.launcher.job.project_calculation')->launch($project);
        $connection = $this->getService('database_connection');
        $this->spin(function () use ($connection) {
            $sql = 'SELECT 1 FROM akeneo_batch_job_execution where status != %s';
            $result = $connection->query(sprintf($sql, BatchStatus::COMPLETED))->fetch();

            return empty($result);
        }, 'The job execution to calculate the teamwork assistant project was too long.');
    }
}
