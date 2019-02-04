@javascript
Feature: Edit a product with localizable and scopable attribute options
  In order to enrich the catalog
  As a regular user
  I need to be able to edit a product with localizable and scopable attribute options

  Background:
    Given the "apparel" catalog configuration
    And the following attributes:
      | code   | label-en_US | label-fr_FR | label-de_DE | type                     | group | scopable | localizable |
      | multi  | Multi       | Multi       | Multi       | pim_catalog_multiselect  | other | 1        | 1           |
      | simple | Simple      | Simple      | Simple      | pim_catalog_simpleselect | other | 1        | 1           |
    And the following family:
      | code   | attributes   |
      | addams | simple,multi |
    And the following products:
      | sku        | family | categories      |
      | rick_morty | addams | 2014_collection |
    And I am logged in as "Peter"
    And the following CSV file to import:
      """
      code;label-fr_FR;label-de_DE;label-en_US;attribute;sort_order
      1;FR1;DE1;US1;multi;1
      2;FR2;DE2;US2;multi;2
      3;FR3;DE3;US3;multi;3
      1;FR1;DE1;US1;simple;1
      2;FR2;DE2;US2;simple;2
      """
    And the following job "option_import" configuration:
      | filePath | %file to import% |
    And I am on the "option_import" import job page
    And I launch the import job
    And I wait for the "option_import" job to finish

  @jira https://akeneo.atlassian.net/browse/PIM-5989
  Scenario: I should not lost data when switching scope on scopable and localizable simple select
    Given I edit the "rick_morty" product
    And I visit the "Other" group
    Then the field Simple should display the Print scope label
    And I change the Simple to "US1"
    When I save the product
    Then I should see the flash message "Product successfully updated"
    Given I switch the scope to "ecommerce"
    Then the field Simple should display the Ecommerce scope label
    And I change the Simple to "US2"
    And I switch the scope to "print"
    When I switch the scope to "ecommerce"
    Then I should see the text "US2"
