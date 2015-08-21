@javascript
Feature: Create product assets
  In order to create product assets
  As an asset manager
  I need to be able to create an asset

  Background:
    Given the "clothing" catalog configuration

  Scenario: Successfully hide entity creation and deletion buttons when user doesn't have the rights
    Given I am logged in as "Peter"
    Then removing "Create an asset" permissions should hide "Create an asset" button on "asset index" page

  Scenario: Create a localized asset
    Given I am logged in as "Pamela"
    And I am on the assets page
    And I press the "Create an asset" button
    Then I should see a dialog with the following content:
      | title | Create a new asset |
    And I switch localizable button to yes
    And I fill the code with new_asset
    When I press the "Save" button
    Then I should be on the "new_asset" asset edit page
    And I should see the reference upload zone
    And I should see the Mobile variation upload zone
    And I should see the Tablet variation upload zone
    And I switch the locale to "French (France)"

  Scenario: Create a non localized asset
    Given I am logged in as "Pamela"
    And I am on the assets page
    And I press the "Create an asset" button
    Then I should see a dialog with the following content:
      | title | Create a new asset |
    And I switch localizable button to no
    And I fill the code with new_asset
    When I press the "Save" button
    Then I should be on the "new_asset" asset edit page
    And I should see the reference upload zone
    And I should see the Mobile variation upload zone
    And I should see the Tablet variation upload zone

  Scenario: Create a non localized asset with a picture
    Given I am logged in as "Pamela"
    And I am on the assets page
    And I press the "Create an asset" button
    Then I should see a dialog with the following content:
      | title | Create a new asset |
    And I switch localizable button to no
    And I upload the reference file akeneo.jpg
    When I press the "Save" button
    Then I should be on the "akeneo" asset edit page
    And I should not be able to generate Mobile from reference
    And I should not be able to generate Tablet from reference
