Feature: Display the category history
  In order to know who, when and what changes has been made to a category
  As a user
  I need to have access to a category history

  @javascript
  Scenario: Display category updates
    Given I am logged in as "admin"
    And I am on the category tree creation page
    When I fill in the following information:
      | Code | book |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
    | action | version | data      |
    | create | 1       | code:book |
    When I visit the "Properties" tab
    And I change the Code to "ebook"
    And I save the category
    And I edit the "ebook" category
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
    | action | version | data           |
    | create | 1       | code:book      |
    | update | 2       | code:bookebook |
