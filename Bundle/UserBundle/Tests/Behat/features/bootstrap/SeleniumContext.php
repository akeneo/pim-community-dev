<?php
// @codingStandardsIgnoreStart
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\ScenarioEvent;
use Oro\Bundle\TestFrameworkBundle\Test\BehatSeleniumContext;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Login;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Navigation;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\User;
use Oro\Bundle\TestFrameworkBundle\Pages\Objects\Users;

class SeleniumContext extends BehatSeleniumContext
{
    // @codingStandardsIgnoreEnd
    /**
     * @BeforeScenario
     */
    public function setup(ScenarioEvent $event)
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort(intval(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT));
        $this->setBrowser(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER);
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
        $this->prepareSession();
    }

    /** @AfterScenario */
    public function teardown(ScenarioEvent $event)
    {
        $this->cookie()->clear();
    }

    /**
     * @Given /^Login as an existing "([^"]*)" user and "([^"]*)" password$/
     */
    public function loginAsAnExistingUserAndPassword($user, $password)
    {
        $login = new Login($this);
        $login->setUsername($user)
             ->setPassword($password)
             ->submit()
             ->assertTitle('Dashboard');
    }

    /**
     * @Given /^I open "([^"]*)" dialog$/
     */
    public function iOpenDialog($dialog)
    {
        $createUser = new Navigation($this);
        $createUser->tab('System')
            ->menu('Users')
            ->openUsers(false)
            ->add()
            ->assertTitle('Create User - Users - System');
    }

    /**
     * @When /^I fill in user form:$/
     */
    public function iFillInUserForm(TableNode $userTable)
    {
        $user = new User($this, false);
        $user->init(true);
        foreach ($userTable->getHash() as $userHash) {
            $this->fillForm($user, $userHash['FIELD'], $userHash['VALUE']);
        }
    }

    /**
     * @param User $form
     * @param string $field
     * @param mixed $value
     */
    protected function fillForm($form, $field, $value)
    {
        switch (strtolower($field)) {
            case 'enabled':
                if ($value) {
                    $form->enable();
                } else {
                    $form->disable();
                }
                break;
            case 'username':
                $form->setUsername($value);
                break;
            case 'password':
                $form->setFirstpassword($value);
                $form->setSecondpassword($value);
                break;
            case 'first name':
                $form->setFirstName($value);
                break;
            case 'last name':
                $form->setLastName($value);
                break;
            case 'email':
                $form->setEmail($value);
                break;
            case 'roles':
                $form->setRoles(array($value));
                break;
        }
    }

    /**
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress($button)
    {
        $user = new User($this, false);
        $user->save()
            ->close();
    }

    /**
     * @Then /^I should see "([^"]*)"$/
     */
    public function iShouldSee($message)
    {
        $users = new Users($this, false);
        $users->assertMessage($message)
            ->assertTitle('Users - System');
    }
}
