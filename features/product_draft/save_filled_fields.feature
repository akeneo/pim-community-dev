@javascript
Feature: Save only filled fields after a save
  In order to avoid to store empty product values
  As a product manager
  I need to be save only filled fields

  Background:
    Given a "clothing" catalog configuration
    Given the following family:
      | code  | attributes                  |
      | socks | sku, name, length, handmade |
    And the following product:
      | sku        | family | categories        | name-en_US |
      | sport-sock | socks  | summer_collection | Socks      |
    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-5597
  Scenario: Successfully display only updated fields by redactor
    Given I edit the "sport-sock" product
    And I visit the "Attributes" tab
    And I change the "Name" to "Socks for sport"
    Then I save the product
    And I press the Send for approval button
    And I logout
    When I am logged in as "Julia"
    And I edit the "sport-sock" product
    And I visit the "Proposal" tab
    Then I should see the following changes on the proposals:
      | product    | author | attribute  |
      | sport-sock | Mary   | name       |
    But I should not see the following changes on the proposals:
      | product    | author | attribute |
      | sport-sock | Mary   | Length    |
      | sport-sock | Mary   | Handmade  |
