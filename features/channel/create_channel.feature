@javascript
Feature: Create a channel
  In order to be able to export data to a new channel
  As a user
  I need to be able to create a channel

  Background:
    Given the following categories:
      | code           | title          |
      | ipad_catalog   | iPad Catalog   |
      | mobile_catelog | Mobile Catalog |
    And the following currencies:
      | code | activated |
      | EUR  | yes       |
      | USD  | yes       |
    And I am logged in as "admin"

  Scenario: Succesfully create a channel
    Given I am on the channel creation page
    Then I should see the Code, Default label, Currencies, Locales and Category tree fields
    And I fill in the following information:
      | Code          | foo            |
      | Default label | bar            |
      | Currencies    | EUR            |
      | Locales       | French         |
      | Category tree | Mobile Catalog |
    And I press the "Save" button
    Then I should see "Channel successfully saved"
