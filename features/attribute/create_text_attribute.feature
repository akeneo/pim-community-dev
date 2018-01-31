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

  Scenario: Fail to create a text attribute with an invalid code
    Given I change the Code to an invalid value
    And I change the "Attribute group" to "Other"
    And I save the attribute
    Then I should see validation error "Attribute code may contain only letters, numbers and underscores"

  @info Codes 'id', associationTypes', 'categories', 'categoryId', 'completeness', 'enabled', 'family', 'groups', 'associations', 'products', 'scope', 'treeId', 'values', '*_groups' and '*_products' are reserved for grid filters and import/export column names
  Scenario: Fail to create a text attribute with a reserved code
    Given I change the "Code" to "categories"
    And I change the "Attribute group" to "Other"
    And I save the attribute
    Then I should see validation error "This code is not available"

  Scenario: Fail to create a text attribute with an invalid validation regex
    Given I fill in the following information:
     | Code               | short_description  |
     | Attribute group    | Other              |
     | Validation rule    | Regular expression |
     | Regular expression | this is not valid  |
    And I save the attribute
    Then I should see validation error "This regular expression is not valid."

  Scenario: Fail to create a text attribute when the group is missing
    Given I fill in the following information:
      | Code | short_description |
    And I save the attribute
    Then I should see validation error "This value should not be blank."

  @jira https://akeneo.atlassian.net/browse/PIM-6324
  Scenario: Successfully switch to tab with an invalid field
    Given I visit the "Label translations" tab
    And I save the attribute
    Then I should see the Code field
    And I should be on the "Properties" tab
    And I should see validation error "This value should not be blank."
