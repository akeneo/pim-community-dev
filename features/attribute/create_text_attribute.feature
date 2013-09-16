Feature: Create an attribute
  In order to be able to define the properties of a product
  As a user
  I need to create a text attribute

  Background:
    Given I am logged in as "admin"
    And I am on the attribute creation page
    And I select the attribute type "Text"

  Scenario: Sucessfully create and validate a text attribute
    Given I fill in the following information:
     | Code | short_description |
    And I save the attribute
    Then I should see "Attribute successfully created"

  Scenario: Fail to create a text attribute with an invalid code
    Given I change the Code to an invalid value
    And I save the attribute
    Then I should see validation error "Attribute code may contain only letters, numbers and underscores"

  @javascript
  Scenario: Fail to create a text attribute with an invalid validation regex
    Given I fill in the following information:
     | Code              | short_descsription |
     | Validation rule   | Regular expression |
     | Validation regexp | this is not valid  |
    And I save the attribute
    Then I should see validation error "This regular expression is not valid."

  @info Codes 'treeId', 'categoryId', 'scope', 'enabled' and 'family' can't be used because there are grid filters with these codes
  Scenario Outline: Fail to create a text attribute with a code that is a reserved word
    Given I change the Code to "<invalid code>"
    And I save the attribute
    Then I should see validation error "This code is not available."

    Examples:
      | invalid code |
      | treeId       |
      | categoryId   |
      | scope        |
      | enabled      |
      | family       |
