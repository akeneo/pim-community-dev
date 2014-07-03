Feature: Display the category history
  In order to know who, when and what changes has been made to a category
  As a product manager
  I need to have access to a category history

  @javascript @skip-doc
  Scenario: Display category updates
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And I am on the category tree creation page
    When I fill in the following information:
      | Code                    | book          |
      | English (United States) | Book category |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property    | value         |
      | 1       | code        | book          |
      | 1       | label-en_US | Book category |
    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | My book category |
    And I save the category
    And I edit the "book" category
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property    | value            |
      | 1       | code        | book             |
      | 1       | label-en_US | Book category    |
      | 2       | label-en_US | My book category |
