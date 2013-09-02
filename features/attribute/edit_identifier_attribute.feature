Feature: Edit an identifier attribute
  In order to specify options for the identifier
  As a user
  I need to edit an identifier attribute

  Scenario: Successfully display the identifier related fields
    Given I am logged in as "admin"
    And I am on the "SKU" attribute page
    Then I should see the Max characters, Validation rule and Searchable fields
    And the fields Unique, Scope, Usable as grid column and Usable as grid filter should be disabled

  @javascript
  Scenario: Succesfully display a message that an identifier already exists when trying to create a second identifier
    Given I am logged in as "admin"
    When I am on the attribute creation page
    And I select the attribute type "Identifier"
    Then I should see validation error "An identifier attribute already exists."

  @javascript
  Scenario: Fail to create a second identifier
    Given the following product attribute:
      | label         | type       |
      | My identifier | identifier |
    And I am logged in as "admin"
    When I am on the attribute creation page
    And I select the attribute type "Identifier"
    And I fill in the following information:
      | Code           | mySecondId |
      | Max characters | 100        |
    And I press the "Save" button
    And I visit the "Parameters" tab
    Then I should see validation error "An identifier attribute already exists."
