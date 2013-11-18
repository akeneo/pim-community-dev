Feature: Display the category history
  In order to know who, when and what changes has been made to a category
  As a user
  I need to have access to a category history

  @javascript
  Scenario: Display category updates
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I am on the category tree creation page
    When I fill in the following information:
      | Code                    | book          |
      | English (United States) | Book category |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
    | action | version | property    | value         |
    | create | 1       | code        | book          |
    | create | 1       | label-en_US | Book category |
    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | My book category |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
    | action | version | property    | value            |
    | create | 1       | code        | book             |
    | create | 1       | label-en_US | Book category    |
    | update | 2       | label-en_US | My book category |
