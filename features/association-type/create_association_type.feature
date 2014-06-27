@javascript
Feature: Association type creation
  In order to create a new type of association type
  As a product manager
  I need to be able to manually create an association type

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the association types page
    And I create a new association type

  @skip
  Scenario: Successfully create an association type
    Then I should see the Code field
    When I fill in the following information in the popin:
      | Code | up_sell |
    And I press the "Save" button
    Then I should be on the "up_sell" association type page
    And I should see "Edit association type - [up_sell]"

  Scenario: Fail to create an association type with an empty or invalid code
    Given I press the "Save" button
    Then I should see validation error "This value should not be blank."
    When I fill in the following information in the popin:
      | Code | =( |
    And I press the "Save" button
    Then I should see validation error "Association type code may contain only letters, numbers and underscores"

  Scenario: Fail to create an association type with an already used code
    Given the following association type:
      | code       |
      | cross_sell |
    When I fill in the following information in the popin:
      | Code | cross_sell |
    And I press the "Save" button
    Then I should see validation error "This value is already used."
