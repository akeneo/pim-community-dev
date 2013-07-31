Feature: Display the family history
  In order to know who, when and what changes has been made to an family
  As a user
  I need to have access to a family history

  @javascript
  Scenario: Display family updates
    Given the following families:
      | code |
      | Shoe |
      | Book |
    And I am logged in as "admin"
    And the following family "Shoe" updates:
      | action | loggedAt  | updatedBy | change                      |
      | update | yesterday | admin     | Default label: shoe => Shoe |
    And I am on the "Book" family page
    And I change the Code to "eBook"
    And I save the family
    When I visit the "History" tab
    Then there should be 1 update
