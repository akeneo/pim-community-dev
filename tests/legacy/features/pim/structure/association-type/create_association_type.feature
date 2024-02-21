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

  @critical
  Scenario: Successfully create an association type
    Then I should see the Code field
    When I fill in the following information in the popin:
      | Code | up_sell |
    And I press the "Save" button
    Then I should see the text "Association type successfully created"
    And I should be on the "up_sell" association type page
    And I should see the text "up_sell"
