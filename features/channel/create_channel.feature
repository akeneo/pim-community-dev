@javascript
Feature: Create a channel
  In order to be able to export data to a new channel
  As an administrator
  I need to be able to create a channel

  Scenario: Succesfully create a channel
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the channel creation page
    Then I should see the Code, Default label, Currencies, Locales and Category tree fields
    And I fill in the following information:
      | Code          | foo             |
      | Default label | bar             |
      | Category tree | 2014 collection |
      | Currencies    | EUR             |
      | Locales       | French          |
    And I press the "Save" button
    Then I should see flash message "Channel successfully saved"
