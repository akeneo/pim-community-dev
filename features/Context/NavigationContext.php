<?php

namespace Context;

use Akeneo\Component\Batch\Model\JobInstance;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Behat\Context\NavigationContext as BaseNavigationContext;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\GroupTypeInterface;
use Pim\Component\Catalog\Model\Product;

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
        $jobExecution = $this->spin(function () use ($code) {
            $jobInstance = $this->getFixturesContext()->getJobInstance($code);
            $this->getFixturesContext()->refresh($jobInstance);

            return $jobInstance->getJobExecutions()->last();
        }, 'Cannot find the last job execution');
        $url = $this->getPage('MassEditJob show')->getUrl(['id' => $jobExecution->getId()]);

        $this->getSession()->visit($url);
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
     * @todo remove when all routes will use `code` for identifier
     * @param string $identifier
     *
     * @Given /^I edit the "([^"]*)" association type$/
     * @Given /^I am on the "([^"]*)" association type page$/
     */
    public function iEditTheAssociationType($identifier)
    {
        $page   = 'AssociationType';
        $this->openPage(sprintf('%s edit', $page), ['code' => $identifier]);
    }

    /**
     * @param string $code
     *
     * @Then /^I should be redirected to the "([^"]*)" (channel|family) page$/
     */
    public function shouldBeRedirectedToTheChannel($code, $page)
    {
        $url = str_replace(
            '{code}',
            $code,
            $this->getPage(sprintf('%s edit', ucfirst($page)))->getUrl()
        );

        $this->spin(function () use ($url) {
            $actualFullUrl = $this->getSession()->getCurrentUrl();
            $result = $actualFullUrl === $url;

            assertTrue($result, sprintf('Expecting to be on page "%s", not "%s"', $url, $actualFullUrl));

            return true;
        }, "Expected to be redirected to channel '%s'", $url);
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
     * @Given /^I am on the ("([^"]*)" export job) page$/
     */
    public function iAmOnTheExportJobPage(JobInstance $job)
    {
        $this->openPage('Export show', ['code' => $job->getCode()]);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I am on the ("([^"]*)" export job) edit page$/
     */
    public function iAmOnTheExportJobEditPage(JobInstance $job)
    {
        $this->openPage('Export edit', ['code' => $job->getCode()]);
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
        $this->currentPage = sprintf('%s creation', $jobType);
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
        $this->openPage(sprintf('%s edit', $page), ['code' => $entity->getCode()]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" attribute group page$/
     */
    public function iAmOnTheAttributeGroupEditPage($identifier)
    {
        $this->openPage('AttributeGroup edit', ['identifier' => $identifier]);
    }

    /**
     * @param string $identifier
     *
     * @Given /^I am on the "([^"]*)" attribute page$/
     */
    public function iAmOnTheAttributeEditPage($identifier)
    {
        $this->openPage('Attribute edit', ['identifier' => $identifier]);
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
        $expectedAddress = $this->getPage($jobPage)->getUrl(['code' => $job->getCode()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param JobInstance $job
     *
     * @Given /^I should be on the ("([^"]*)" (import|export) job) edit page$/
     */
    public function iShouldBeOnTheJobEditPage(JobInstance $job)
    {
        $jobPage         = sprintf('%s edit', ucfirst($job->getType()));
        $expectedAddress = $this->getPage($jobPage)->getUrl(['code' => $job->getCode()]);
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param GroupTypeInterface $groupType
     *
     * @Given /^I should be on the ("([^"]*)" group type) page$/
     */
    public function iShouldBeOnTheGroupTypePage(GroupTypeInterface $groupType)
    {
        $expectedAddress = $this->getPage('GroupType edit')->getUrl(['code' => $groupType->getCode()]);
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
     * @param AssociationTypeInterface $associationType
     *
     * @Given /^I should be on the ("([^"]*)" association type) page$/
     */
    public function iShouldBeOnTheAssociationTypePage(AssociationTypeInterface $associationType)
    {
        $expectedAddress = $this->getPage('AssociationType edit')->getUrl(['code' => $associationType->getCode()]);
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

        $this->spin(function () use ($expectedAddress) {
            $this->assertAddress($expectedAddress);

            return true;
        }, sprintf('Expected to be on the %s category node creation page. But was not', $category->getCode()));
    }

    /**
     * @Given /^I should be on the products page$/
     */
    public function iShouldBeOnTheProductsPage()
    {
        $expectedAddress = $this->getPage('Product index')->getUrl();
        $this->assertAddress($expectedAddress);
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
        }, sprintf('Cannot find product "%s"', $product->getId()));

        $this->getMainContext()->spin(function () {
            return $this->getCurrentPage()->find('css', '.AknTitleContainer-title');
        }, 'Can not find any product label');

        $this->currentPage = 'Product edit';
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }
}
