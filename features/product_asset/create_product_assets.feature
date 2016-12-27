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
    And I switch the locale to "fr_FR"
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
    And the locale "fr_FR" should be selected

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

  Scenario: Successfully increment an existing asset code
    Given I am logged in as "Pamela"
    And I am on the assets page
    When I press the "Create an asset" button
    And I fill the code with random_asset
    And I press the "Save" button
    Then I should be on the "random_asset" asset edit page
    When I am on the assets page
    And I press the "Create an asset" button
    And I fill the code with random_asset and wait for validation
    And I hover over the element ".validation-tooltip"
    Then I should see the text "Code must be unique. We generated a new one for you."

  @jira https://akeneo.atlassian.net/browse/PIM-6023
  Scenario: Successfully create an asset with a non existent similar code
    Given I am logged in as "Pamela"
    And I am on the assets page
    When I press the "Create an asset" button
    And I fill the code with random_asset_1
    And I press the "Save" button
    Then I should be on the "random_asset_1" asset edit page
    When I am on the assets page
    And I press the "Create an asset" button
    And I fill the code with random_asset
    And I press the "Save" button
    Then I should be on the "random_asset" asset edit page
