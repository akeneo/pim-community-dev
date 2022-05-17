<?php

declare(strict_types=1);

namespace Context;

use Akeneo\Channel\Infrastructure\Doctrine\Repository\LocaleRepository;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;
use Webmozart\Assert\Assert;

class EnterprisePermissionContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Then /^I should see the category permission (.*) with user groups (.*)$/
     */
    public function iShouldSeeTheCategoryPermissionWithUserGroups(string $fieldLabel, string $userGroups)
    {
        /** @var NodeElement $field */
        $values = $this->spin(function () use ($fieldLabel) {
            $field = $this->getCurrentPage()->findField($fieldLabel);
            if (!$field) {
                return false;
            }

            $itemList = $field->getParent()->getParent()->findAll('css', 'li');

            return array_filter(
                array_map(fn (NodeElement $item) => trim($item->getText()), $itemList),
                fn (string $content) => !empty($content)
            );
        }, sprintf('Field "%s" not found', $fieldLabel));
        $expectedGroups = $this->listToArray($userGroups);

        Assert::allInArray($expectedGroups, $values);
    }

    /**
     * @When I fill in the category permission with:
     */
    public function iFillInTheCategoryPermissionWith(TableNode $table)
    {
        foreach ($table->getRowsHash() as $field => $value) {
            $userGroups = $this->listToArray($value);

            // Reset the values before adding fill in with user groups
            $this->iRemoveAllTheCategoryPermission($field);

            $this->spin(function () use ($field, $userGroups) {
                $inputField = $this->getCurrentPage()->findField($field);
                if (!$inputField) {
                    return false;
                }

                // shows the list of options
                $inputField->focus();

                $backdrop = $this->getCurrentPage()->find('css', 'div[data-testid="backdrop"]');
                if (!$backdrop) {
                    return false;
                }

                $overlayRoot = $this->getCurrentPage()->findById('input-overlay-root');
                foreach ($userGroups as $userGroup) {
                    $option = $overlayRoot->find('named', ['content', $userGroup]);
                    if (!$option) {
                        return false;
                    }

                    $option->click();
                }

                $this->closeBackdrop();
                return true;
            }, sprintf('Cannot fill the field %s', $field));
        }
    }

    /**
     * @When I remove all the category permission from :field
     */
    public function iRemoveAllTheCategoryPermission(string $field)
    {
        $this->spin(function () use ($field) {
            $inputField = $this->getCurrentPage()->findField($field);
            if (!$inputField) {
                return false;
            }

            do {
                $removeButton = $inputField->getParent()->getParent()->find('css', 'button');
                if ($removeButton !== null) {
                    $removeButton->press();
                }
            } while ($removeButton !== null);

            return true;
        }, sprintf('Cannot remove permission on the field %s', $field));
    }

    /**
     * Cannot click on backdrop with behat because when behat click, it click the middle of the element at this position it's the option list
     */
    private function closeBackdrop()
    {
        $this->getSession()->executeScript("document.querySelector('[data-testid=backdrop]').click()");
    }

    /**
     * @Then /^user group "([^"]*)" should have the following locale permissions:$/
     */
    public function userGroupShouldHaveTheFollowingLocalePermissions(string $userGroupName, TableNode $table)
    {
        /** @var LocaleRepository $localRepository */
        $localeRepository = $this->getMainContext()->getContainer()->get('pim_catalog.repository.locale');

        /** @var LocaleAccessManager $accessManager */
        $localeAccessManager = $this->getMainContext()->getContainer()->get('pimee_security.manager.locale_access');

        foreach ($table->getHash() as $data) {
            $locale = $localeRepository->findOneByIdentifier($data['locale']);

            $localeViewUserGroupsNames = array_map(
                fn (GroupInterface $userGroup) => $userGroup->getName(),
                $localeAccessManager->getViewUserGroups($locale),
            );
            $localeEditUserGroupsNames = array_map(
                fn (GroupInterface $userGroup) => $userGroup->getName(),
                $localeAccessManager->getEditUserGroups($locale),
            );

            $currentAccesses = [];
            if (in_array($userGroupName, $localeViewUserGroupsNames)) {
                $currentAccesses[] = 'view';
            }
            if (in_array($userGroupName, $localeEditUserGroupsNames)) {
                $currentAccesses[] = 'edit';
            }
            sort($currentAccesses);

            $expectedAccesses = 'none' === $data['accesses'] ? [] : array_map('trim', explode(',', $data['accesses']));
            sort($expectedAccesses);

            Assert::same($expectedAccesses, $currentAccesses);
        }
    }
}
