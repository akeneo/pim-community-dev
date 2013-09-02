Feature: Display the attribute history
  In order to know who, when and what changes has been made to an attribute
  As a user
  I need to have access to a attribute history

  @javascript
  Scenario: Display attribute updates
    Given the following product attributes:
      | label        | type |
      | Brand        | text |
      | Manufacturer | text |
    And I am logged in as "admin"
    And the following attribute "Brand" updates:
      | action | loggedAt  | updatedBy | change             |
      | update | yesterday | admin     | max_characters: '' => 50 |
    And I am on the "Manufacturer" attribute page
    When I visit the "Parameters" tab
    And I change the "Max characters" to "30"
    And I save the attribute
    When I visit the "History" tab
    Then there should be 1 update
