Feature: Display the association history
  In order to know who, when and what changes has been made to an association
  As a user
  I need to have access to an association history

  @javascript
  Scenario: Successfully edit an association and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the associations page
    When I create a new association
    And I fill in the following information in the popin:
      | Code | REPLACEMENT |
    And I press the "Save" button
    And I am on the associations page
    Then I should see association REPLACEMENT
    When I am on the "REPLACEMENT" association page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | property | value       |
      | create | 1       | code     | REPLACEMENT |
