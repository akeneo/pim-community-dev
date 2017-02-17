@javascript
Feature: Create an attribute
  In order to be able to define the properties of a product
  As a product manager
  I need to create a text attribute

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page
    And I create a "Text" attribute

  Scenario: Successfully create and validate a text attribute
    Given I fill in the following information:
      | Code            | short_description |
      | Attribute group | Other             |
    And I save the attribute
    Then I should see the flash message "Attribute successfully created"

  @info Codes 'id', associationTypes', 'categories', 'categoryId', 'completeness', 'enabled', 'family', 'groups', 'associations', 'products', 'scope', 'treeId', 'values', '*_groups' and '*_products' are reserved for grid filters and import/export column names
  Scenario: Fail to create a text attribute with an invalid or reserved code
    Given I change the Code to an invalid value
    And I scroll down
    And I change the "Attribute group" to "Other"
    And I save the attribute
    Then I should see validation error "Attribute code may contain only letters, numbers and underscores"
    And the following attribute codes should not be available:
      | code             | group | type             |
      | id               | other | pim_catalog_text |
      | associationTypes | other | pim_catalog_text |
      | category         | other | pim_catalog_text |
      | categoryId       | other | pim_catalog_text |
      | categories       | other | pim_catalog_text |
      | completeness     | other | pim_catalog_text |
      | enabled          | other | pim_catalog_text |
      | family           | other | pim_catalog_text |
      | groups           | other | pim_catalog_text |
      | associations     | other | pim_catalog_text |
      | products         | other | pim_catalog_text |
      | scope            | other | pim_catalog_text |
      | treeId           | other | pim_catalog_text |
      | values           | other | pim_catalog_text |
      | my_groups        | other | pim_catalog_text |
      | my_products      | other | pim_catalog_text |

  Scenario: Fail to create a text attribute with an invalid validation regex
    Given I fill in the following information:
     | Code              | short_description  |
     | Validation rule   | Regular expression |
     | Validation regexp | this is not valid  |
     | Attribute group   | Other              |
    And I save the attribute
    Then I should see validation error "This regular expression is not valid."

  Scenario: Fail to create a text attribute when the group is missing
    Given I fill in the following information:
      | Code | short_description |
    And I save the attribute
    Then I should see validation error "This value should not be blank."
