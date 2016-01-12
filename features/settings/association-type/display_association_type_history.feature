Feature: Display the association type history
  In order to know who, when and what changes has been made to an association type
  As a product manager
  I need to have access to an association history

  @javascript
  Scenario: Successfully edit an association type and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the association types page
    When I create a new association type
    And I fill in the following information in the popin:
      | Code | REPLACEMENT |
    And I press the "Save" button
    And I am on the association types page
    Then I should see association type REPLACEMENT
    When I am on the "REPLACEMENT" association type page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value       |
      | 1       | code     | REPLACEMENT |
