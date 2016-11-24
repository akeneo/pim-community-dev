@javascript
Feature: Edit a product with localizable and scopable attribute options
  In order to enrich the catalog
  As a regular user
  I need to be able to edit a product with localizable and scopable attribute options

  Background:
    Given the "apparel" catalog configuration
    And the following products:
      | sku        | categories      |
      | rick_morty | 2014_collection |
    And the following attributes:
      | code   | label-en_US | label-fr_FR | label-de_DE | type         | group | scopable | localizable |
      | simple | Simple      | Simple      | Simple      | simpleselect | other | yes      | yes         |
    And I am logged in as "Peter"
    And the following CSV file to import:
      """
      code;label-fr_FR;label-de_DE;label-en_US;attribute;sort_order
      1;FR1;DE1;US1;simple;1
      2;FR2;DE2;US2;simple;2
      """
    And the following job "option_import" configuration:
      | filePath      | %file to import% |
    And I am on the "option_import" import job page
    And I launch the import job
    And I wait for the "option_import" job to finish

  @jira https://akeneo.atlassian.net/browse/PIM-5989
  Scenario: I should not lost data when switching scope on scopable and localizable simple select
    Given I edit the "rick_morty" product
    And I add available attribute Simple
    And I change the Simple to "US1"
    When I save the product
    Then I should see the flash message "Product successfully updated"
    Given I switch the scope to "ecommerce"
    And I change the Simple to "US2"
    And I switch the scope to "print"
    When I switch the scope to "ecommerce"
    Then I should see the text "US2"
