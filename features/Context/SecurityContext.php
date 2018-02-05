<?php

namespace Context;


use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class SecurityContext extends RawMinkContext implements KernelAwareInterface
{
    /** @var KernelInterface */
    protected $kernel;

    /** @var Client */
    protected $client;

    /**
     * @param string $baseUrl
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" user group$/
     */
    public function iMakeADirectCallToDeleteTheUserGroup($userGroupLabel)
    {
        $routeName = 'oro_user_group_delete';

        $userGroup = $this->kernel
            ->getContainer()
            ->get('pim_user.repository.group')
            ->findOneByIdentifier($userGroupLabel);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $userGroup->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" user role$/
     */
    public function iMakeADirectCallToDeleteTheUserRole($role)
    {
        $routeName = 'oro_user_role_delete';

        $userRole = $this->kernel
            ->getContainer()
            ->get('pim_user.repository.role')
            ->findOneByIdentifier($role);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $userRole->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" user$/
     */
    public function iMakeADirectCallToDeleteTheUser($username)
    {
        $routeName = 'oro_user_user_delete';

        $user = $this->kernel
            ->getContainer()
            ->get('pim_user.repository.user')
            ->findOneByIdentifier($username);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $user->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the last comment of "([^"]*)" product$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheLastCommentOfProduct($productIdentifier)
    {
        $routeName = 'pim_comment_comment_delete';

        $product = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.product')
            ->findOneByIdentifier($productIdentifier);

        $comments = $this->kernel
            ->getContainer()
            ->get('pim_comment.repository.comment')
            ->getComments(
                ClassUtils::getClass($product),
                $product->getId()
            );

        $lastComment = end($comments);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $lastComment->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" datagrid view as "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheDatagridView($datagridViewLabel, $username)
    {
        $routeName = 'pim_datagrid_view_remove';

        $view = $this->kernel
            ->getContainer()
            ->get('pim_datagrid.repository.datagrid_view')
            ->findOneBy(['label' => $datagridViewLabel]);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $view->getId()]);

        $this->doCall('DELETE', $url, [], [], $username);
    }

    /**
     * @When /^I make a direct authenticated GET call to mass delete "([^"]*)" product$/
     */
    public function iMakeADirectAuthenticatedGetCallToMassDeleteProduct($productIndentifier)
    {
        $routeName = 'pim_datagrid_mass_action';

        $product = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.product')
            ->findOneByIdentifier($productIndentifier);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['gridName' => 'product-grid', 'actionName' => 'delete']);

        $this->doCall('GET', $url, [
            'inset' => 1,
            'values' => $product->getId()
        ]);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" association type$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAssociationType($associationTypeCode)
    {
        $routeName = 'pim_enrich_associationtype_remove';

        $associationType = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.association_type')
            ->findOneByIdentifier($associationTypeCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $associationType->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" attribute$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAttribute($attributeCode)
    {
        $routeName = 'pim_enrich_attribute_remove';

        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $attribute->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" attribute group$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAttributeGroup($attributeGroupCode)
    {
        $routeName = 'pim_enrich_attributegroup_remove';

        $attributeGroup = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_group')
            ->findOneByIdentifier($attributeGroupCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, ['id' => $attributeGroup->getId()]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" attribute in the "([^"]*)" attribute group$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAttributeInTheAttributeGroup($attributeCode, $attributeGroupCode)
    {
        $routeName = 'pim_enrich_attributegroup_removeattribute';

        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        $attributeGroup = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_group')
            ->findOneByIdentifier($attributeGroupCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, [
                'groupId' => $attributeGroup->getId(),
                'attributeId' => $attribute->getId(),
            ]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated POST call to create a "([^"]*)" attribute option for attribute "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedPostCallToCreateAAttributeOptionForAttribute($attributeOptionCode, $attributeCode)
    {
        $routeName = 'pim_enrich_attributeoption_create';

        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, [
                'attributeId' => $attribute->getId(),
            ]);

        $this->doCall('POST', $url, [], [
            'code' => $attributeOptionCode
        ]);
    }

    /**
     * @When /^I make a direct authenticated PUT call to update the "([^"]*)" attribute option for attribute "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedPutCallToUpdateTheAttributeOptionForAttribute($attributeOptionCode, $attributeCode)
    {
        $routeName = 'pim_enrich_attributeoption_update';

        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        $attributeOption = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier(sprintf('%s.%s', $attributeCode, $attributeOptionCode));

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, [
                'attributeId' => $attribute->getId(),
                'attributeOptionId' => $attributeOption->getId(),
            ]);

        $this->doCall('PUT', $url, [], [
            'code' => 'csrf_test',
            'id' => $attributeOption->getId()
        ]);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" attribute option for attribute "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheAttributeOptionForAttribute($attributeOptionCode, $attributeCode)
    {
        $routeName = 'pim_enrich_attributeoption_delete';

        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        $attributeOption = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier(sprintf('%s.%s', $attributeCode, $attributeOptionCode));

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, [
                'attributeId' => $attribute->getId(),
                'attributeOptionId' => $attributeOption->getId(),
            ]);

        $this->doCall('DELETE', $url);
    }

    /**
     * @When /^I make a direct authenticated PUT call to sort attribute options of attribute "([^"]*)"$/
     */
    public function iMakeADirectAuthenticatedPutCallToSortAttributeOptionsOfAttribute($attributeCode)
    {
        $routeName = 'pim_enrich_attributeoption_update_sorting';

        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, [
                'attributeId' => $attribute->getId(),
            ]);

        $attributeOptions = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_option')
            ->getOptions('en_US', $attribute->getId())['results'];

        $attributeOptionIds = array_map(function ($attributeOption) {
            return $attributeOption['id'];
        }, $attributeOptions);

        $attributeOptionIds = array_reverse($attributeOptionIds);

        $this->doCall('PUT', $url, [], $attributeOptionIds);
    }

    /**
     * @When /^I make a direct authenticated POST call to move the "([^"]*)" category into the "([^"]*)" category$/
     */
    public function iMakeADirectAuthenticatedPostCallToMoveTheCategoryIntoTheCategory($childCategoryCode, $parentCategoryCode)
    {
        $routeName = 'pim_enrich_categorytree_movenode';

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName);

        $childCategory = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.category')
            ->findOneByIdentifier($childCategoryCode);

        $parentCategory = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.category')
            ->findOneByIdentifier($parentCategoryCode);

        $this->doCall('POST', $url, [
            'id' => $childCategory->getId(),
            'parent' => $parentCategory->getId(),
            'prev_sibling' => null,
            'position' => 0,
            'copy' => 0,
        ]);
    }

    /**
     * @When /^I make a direct authenticated DELETE call on the "([^"]*)" category$/
     */
    public function iMakeADirectAuthenticatedDeleteCallOnTheCategory($categoryCode)
    {
        $routeName = 'pim_enrich_categorytree_remove';

        $category = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.category')
            ->findOneByIdentifier($categoryCode);

        $url = $this->kernel
            ->getContainer()
            ->get('router')
            ->generate($routeName, [
                'id' => $category->getId(),
            ]);

        $this->doCall('DELETE', $url);
    }

//    /**
//     * @When /^I make a direct authenticated POST call on the "([^"]*)" user group with following data:$/
//     */
//    public function iMakeADirectAuthenticatedPostCallOnTheUserGroupWithFollowingData($userGroupCode, TableNode $table)
//    {
//        $routeName = 'oro_user_group_update';
//        $params = [];
//
//        foreach ($table->getRows() as $data) {
//            $params[$data[0]] = $data[1];
//        }
//
//        $userGroup = $this->kernel
//            ->getContainer()
//            ->get('pim_user.repository.group')
//            ->findOneByIdentifier($userGroupCode);
//
//        $url = $this->kernel
//            ->getContainer()
//            ->get('router')
//            ->generate($routeName, ['id' => $userGroup->getId()]);
//
//
//        $this->doCall('POST', $url, $params);
//        var_dump($params);
//    }

    /**
     * @Then /^the category "([^"]*)" should have "([^"]*)" as parent$/
     */
    public function theCategoryShouldHaveAsParent($childCategoryCode, $parentCategoryCode)
    {
        $childCategory = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.category')
            ->findOneByIdentifier($childCategoryCode);

        assertEquals($parentCategoryCode, $childCategory->getParent()->getCode());
    }

    /**
     * @Then /^the order for attribute options "([^"]*)" of attribute "([^"]*)" should be (\d+)$/
     */
    public function theOrderForAttributeOptionsOfAttributeShouldBe($attributeOptionCode, $attributeCode, $order)
    {
        $attributeOption = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier(sprintf('%s.%s', $attributeCode, $attributeOptionCode));

        assertEquals($order, $attributeOption->getSortOrder());
    }

    /**
     * @Then /^there should be a "([^"]*)" association type$/
     */
    public function thereShouldBeAAssociationType($associationTypeCode)
    {
        $associationType = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.association_type')
            ->findOneByIdentifier($associationTypeCode);

        assertNotNull($associationType);
    }

    /**
     * @Then /^there should be a "([^"]*)" category$/
     */
    public function thereShouldBeACategory($categoryCode)
    {
        $category = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.category')
            ->findOneByIdentifier($categoryCode);

        assertNotNull($category);
    }

    /**
     * @Then /^there should be a "([^"]*)" attribute group$/
     */
    public function thereShouldBeAAttributeGroup($attributeGroupCode)
    {
        $attributeGroup = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_group')
            ->findOneByIdentifier($attributeGroupCode);

        assertNotNull($attributeGroup);
    }

    /**
     * @Then /^there should be a "([^"]*)" attribute$/
     */
    public function thereShouldBeAAttribute($attributeCode)
    {
        $attribute = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute')
            ->findOneByIdentifier($attributeCode);

        assertNotNull($attribute);
    }

    /**
     * @Then /^there should be a "([^"]*)" datagrid view$/
     */
    public function thereShouldBeADatagridView($datagridViewLabel)
    {
        $view = $this->kernel
            ->getContainer()
            ->get('pim_datagrid.repository.datagrid_view')
            ->findOneBy(['label' => $datagridViewLabel]);

        assertNotNull($view);
    }

    /**
     * @Then /^there should be a "([^"]*)" product$/
     */
    public function thereShouldBeAProduct($productIdentifier)
    {
        $product = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.product')
            ->findOneByIdentifier($productIdentifier);

        assertNotNull($product);
    }

    /**
     * @Then /^there should( not)? be a "([^"]*)" attribute option for attribute "([^"]*)"$/
     */
    public function thereShouldNotBeAAttributeOptionForAttribute($not, $attributeOptionCode, $attributeCode)
    {
        $attributeOption = $this->kernel
            ->getContainer()
            ->get('pim_catalog.repository.attribute_option')
            ->findOneByIdentifier(sprintf('%s.%s', $attributeCode, $attributeOptionCode));

        if ($not) {
            assertNull($attributeOption);
        } else {
            assertNotNull($attributeOption);
        }
    }

    /**
     * @param string $method
     * @param string $url
     */
    private function doCall($method, $url, $data = [], $content = [], $username = 'Julia')
    {
        $this->logIn($username);
        $this->client->request($method, $url, $data, [], [], json_encode($content));

//        print_r($this->client->getResponse()->getContent());
//        print_r($this->client->getResponse()->getStatusCode());
    }

    /**
     * @param string $username
     */
    private function logIn($username = 'Julia')
    {
        // http://symfony.com/doc/current/testing/http_authentication.html

        $client = new Client($this->kernel);
        $client->disableReboot();
        $client->followRedirects();
        $this->client = $client;

        $session = $this->client->getContainer()->get('session');

        $user = $this->kernel
            ->getContainer()
            ->get('pim_user.repository.user')
            ->findOneBy(['username' => $username]);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
