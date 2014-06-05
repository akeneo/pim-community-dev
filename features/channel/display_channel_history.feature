Feature: Display the channel history
  In order to know who, when and what changes has been made to a channel
  As an administrator
  I need to have access to a channel history

  @javascript
  Scenario: Successfully edit a channel and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the channel creation page
    And I fill in the following information:
      | Code          | foo             |
      | Default label | bar             |
      | Category tree | 2014 collection |
      | Currencies    | EUR             |
      | Locales       | French (France) |
    And I press the "Save" button
    When I am on the "foo" channel page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property   | value           |
      | 1       | code       | foo             |
      | 1       | label      | bar             |
      | 1       | category   | 2014_collection |
      | 1       | currencies | EUR             |
      | 1       | locales    | fr_FR           |
