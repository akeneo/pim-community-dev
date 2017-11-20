@javascript
Feature: Edit an attribute group
  In order to manage existing attribute groups in the catalog
  As a product manager
  I need to be able to edit an attribute group

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit an attribute group
    Given I am on the "sizes" attribute group page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My sizes |
    And I press the "Save" button
    Then I should see the text "My sizes"

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "media" attribute group page
    When I fill in the following information:
      | English (United States) | My media |
    And I click on the Akeneo logo
    Then I should see "You will lose changes to the attribute group if you leave this page." in popup

  @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "media" attribute group page
    When I fill in the following information:
      | English (United States) | My media |
    Then I should see the text "There are unsaved changes."

  @skip-pef @javascript @jira https://akeneo.atlassian.net/browse/PIM-6434
  Scenario: Successfully display attribute groups in the right order
    Given the following CSV file to import:
      """
      code;attributes;sort_order
      Z;sole_fabric;100
      Y;length;300
      X;weight;200
      """
    And the following job "csv_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    And I am on the "csv_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_group_import" job to finish
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | a_boot |
      | family | Boots  |
    And I press the "Save" button in the popin
    And I wait to be on the "a_boot" product page
    When I add available attributes Sole fabric, Length, Weight
    Then I should see the text "[Z] [X] [Y]"
