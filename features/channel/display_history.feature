Feature: Display the channel history
  In order to know who, when and what changes has been made to a channel
  As a user
  I need to have access to a channel history

  @javascript
  Scenario: Successfully edit a channel and see the history
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the channel creation page
    And I fill in the following information:
      | Code          | foo             |
      | Default label | bar             |
      | Category tree | 2014 collection |
    And I select the currency "EUR"
    And I select the locale "French (France)"
    And I press the "Save" button
    When I am on the "foo" channel page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | property      | value           |
      | create | 1       | code          | foo             |
      | create | 1       | label         | bar             |
      | create | 1       | category | 2014_collection |
      | create | 1       | currencies    | EUR             |
      | create | 1       | locales       | fr_FR           |
