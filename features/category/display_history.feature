Feature: Display the category history
  In order to know who, when and what changes has been made to a category
  As a user
  I need to have access to a category history

  @javascript
  Scenario: Display category updates
    Given the following categories:
      | code | title |
      | shoe | Shoe  |
      | book | Book  |
    And I am logged in as "admin"
    And the following category "shoe" updates:
      | action | loggedAt  | updatedBy | change               |
      | update | yesterday | admin     | title: Shoe => Shoes |
    And I am on the "book" category page
    And I change the Code to "eBook"
    And I save the category
    When I visit the "History" tab
    Then there should be 2 updates
