<?php

namespace Context;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Event\BaseScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Behat\Context\NavigationContext as BaseNavigationContext;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;
use Pim\Bundle\CatalogBundle\Model\Product;

/**
 * Context for navigating the website
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NavigationContext extends BaseNavigationContext
{
    /**
     * @param string $code
     *
     * @When /^I go on the last executed job resume of "([^"]*)"$/
     */
    public function iGoOnTheLastExecutedJobResume($code)
    {
        $this->wait();
        $jobInstance   = $this->getFixturesContext()->getJobInstance($code);
        $jobExecutions = $jobInstance->getJobExecutions();

        $url = $this->getPage('MassEditJob show')->getUrl(['id' => $jobExecutions->last()->getId()]);
        $this->getSession()->visit($this->locatePath($url));
        $this->wait();
    }

    /**
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" user group$/
     */
    public function iEditTheUserGroup($identifier)
    {
        $page   = 'UserGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('UserGroup edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $label
     *
     * @Given /^I edit the "([^"]+)" user role$/
     */
    public function iEditTheUserRole($label)
    {
        $page   = 'UserRole';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($label);
        $this->openPage(sprintf('UserRole edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" association type$/
     */
    public function iEditTheAssociationType($identifier)
    {
        $page   = 'AssociationType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param Category $category
     *
     * @Given /^I am on the (category "([^"]*)") node creation page$/
     */
    public function iAmOnTheCategoryNodeCreationPage(Category $category)
    {
        $this->openPage('Category node creation', ['id' => $category->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) page$/
     */
    public function iAmOnTheImportJobPage(JobInstance $job)
    {
        $this->openPage('Import show', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) page$/
     */
    public function iAmOnTheExportJobPage(JobInstance $job)
    {
        $this->openPage('Export show', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" import job) edit page$/
     */
    public function iAmOnTheImportJobEditPage(JobInstance $job)
    {
        $this->openPage('Import edit', ['id' => $job->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) edit page$/
     */
    public function iAmOnTheExportJobEditPage(JobInstance $job)
    {
        $this->openPage('Export edit', ['id' => $job->getId()]);
    }

    /**
     * @param string $jobTitle
     * @param string $jobType
     *
     * @Given /^I create a new "([^"]*)" (import|export)$/
     */
    public function iCreateANewJob($jobTitle, $jobType)
    {
        $jobType = ucfirst($jobType);
        $this->getPage(sprintf('%s index', $jobType))->clickJobCreationLink($jobTitle);
        $this->wait();
        $this->currentPage = sprintf('%s creation', $jobType);
    }

    /**
     * @param string $jobType
     *
     * @Given /^I try to create an unknown (import|export)$/
     */
    public function iTryToCreateAnUnknownJob($jobType)
    {
        $this->openPage(sprintf('%s creation', ucfirst($jobType)));
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" product group page$/
     * @Given /^I edit the "([^"]*)" product group$/
     */
    public function iAmOnTheProductGroupEditPage($identifier)
    {
        $page   = 'ProductGroup';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" group type page$/
     * @Given /^I edit the "([^"]*)" group type$/
     */
    public function iAmOnTheGroupTypeEditPage($identifier)
    {
        $page   = 'GroupType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" association type page$/
     */
    public function iAmOnTheAssociationTypeEditPage($identifier)
    {
        $page   = 'AssociationType';
        $getter = sprintf('get%s', $page);
        $entity = $this->getFixturesContext()->$getter($identifier);
        $this->openPage(sprintf('%s edit', $page), ['id' => $entity->getId()]);
    }

    /**
     * @param JobInstance $job
     *
     * @When /^I launch the ("([^"]*)" (import|export) job)$/
     */
    public function iLaunchTheExportJob(JobInstance $job)
    {
        $jobType = ucfirst($job->getType());
        $this->openPage(sprintf('%s launch', $jobType), ['id' => $job->getId()]);
    }

    /**
     * @param string      $action
     * @param JobInstance $job
     *
     * @return \Behat\Behat\Context\Step\Then
     *
     * @When /^I should not be able to (launch|edit) the ("([^"]*)" (export|import) job)$/
     */
    public function iShouldNotBeAbleToAccessTheJob($action, JobInstance $job)
    {
        $this->currentPage = sprintf("%s %s", ucfirst($job->getType()), $action);
        $page              = $this->getCurrentPage()->open(['id' => $job->getId()]);

        return new Step\Then('I should see "403 Forbidden"');
    }

    /**
     * @param AttributeGroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" attribute group) page$/
     */
    public function iShouldBeOnTheAttributeGroupPage(AttributeGroupInterface $group)
    {
        $expectedAddress = $this->getPage('AttributeGroup edit')->getUrl(['id' => $group->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I should be on the ("([^"]*)" (import|export) job) page$/
     */
    public function iShouldBeOnTheJobPage(JobInstance $job)
    {
        $jobPage         = sprintf('%s show', ucfirst($job->getType()));
        $expectedAddress = $this->getPage($jobPage)->getUrl(['id' => $job->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupTypeInterface $groupType
     *
     * @Given /^I should be on the ("([^"]*)" group type) page$/
     */
    public function iShouldBeOnTheGroupTypePage(GroupTypeInterface $groupType)
    {
        $expectedAddress = $this->getPage('GroupType edit')->getUrl(['id' => $groupType->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupInterface $group
     *
     * @Given /^I should be on the ("([^"]*)" product group) page$/
     */
    public function iShouldBeOnTheProductGroupPage(GroupInterface $group)
    {
        $expectedAddress = $this->getPage('ProductGroup edit')->getUrl(['id' => $group->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Role $role
     *
     * @Given /^I should be on the ("([^"]*)" role) page$/
     */
    public function iShouldBeOnTheRolePage(Role $role)
    {
        $expectedAddress = $this->getPage('Role edit')->getUrl(['id' => $role->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Then /^I should be on the user groups edit page$/
     */
    public function iShouldBeOnTheUserGroupsEditPage()
    {
        $this->assertAddress($this->getPage('UserGroup edit')->getUrl());
    }

    /**
     * @param Family $family
     *
     * @Given /^I should be on the ("([^"]*)" family) page$/
     */
    public function iShouldBeOnTheFamilyPage(Family $family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(['id' => $family->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param AssociationTypeInterface $associationType
     *
     * @Given /^I should be on the ("([^"]*)" association type) page$/
     */
    public function iShouldBeOnTheAssociationTypePage(AssociationTypeInterface $associationType)
    {
        $expectedAddress = $this->getPage('AssociationType edit')->getUrl(['id' => $associationType->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the locales page$/
     */
    public function iShouldBeOnTheLocalesPage()
    {
        $this->assertAddress($this->getPage('Locale index')->getUrl());
    }

    /**
     * @Given /^I should be on the families page$/
     */
    public function iShouldBeOnTheFamiliesPage()
    {
        $this->assertAddress($this->getPage('Family index')->getUrl());
    }

    /**
     * @Given /^I should be on the attributes page$/
     */
    public function iShouldBeOnTheAttributesPage()
    {
        $this->assertAddress($this->getPage('Attribute index')->getUrl());
    }

    /**
     * @Given /^I should be on the categories page$/
     */
    public function iShouldBeOnTheCategoriesPage()
    {
        $this->assertAddress($this->getPage('Category index')->getUrl());
    }

    /**
     * @param Category $category
     *
     * @Then /^I should be on the (category "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheCategoryEditPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category edit')->getUrl(['id' => $category->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param Category $category
     *
     * @Given /^I should be on the (category "([^"]*)") node creation page$/
     */
    public function iShouldBeOnTheCategoryNodeCreationPage(Category $category)
    {
        $expectedAddress = $this->getPage('Category node creation')->getUrl(['id' => $category->getId()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @Given /^I should be on the products page$/
     */
    public function iShouldBeOnTheProductsPage()
    {
        $expectedAddress = $this->getPage('Product index')->getUrl();
        $this->assertAddress($expectedAddress);
        $this->wait();
    }

    /**
     * @param Product $product
     *
     * @Given /^I should be on the (product "([^"]*)") edit page$/
     */
    public function iShouldBeOnTheProductEditPage(Product $product)
    {
        $this->spin(function () use ($product) {
            $expectedAddress = $this->getPage('Product edit')->getUrl(['id' => $product->getId()]);
            $this->assertAddress($expectedAddress);

            return true;
        });

        $this->getMainContext()->spin(function () {
            return $this->getCurrentPage()->find('css', '.product-label');
        });
    }

    /**
     * @param int    $time
     * @param string $condition
     */
    protected function wait($time = 10000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
