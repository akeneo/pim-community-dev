@javascript
Feature: Send a product draft for approval
  In order to apply my product draft
  As a contributor
  I need to be able to send my product draft for approval

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | pants      |
      | categories | winter_top |
      | sku        | my-pant    |
    And I am logged in as "Mary"
    And I edit the "my-pant" product

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully create a new product draft
    When I change the "Name" to "Baggy"
    And I save the product
    Then its status should be "In progress"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully send my product draft for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    Then its status should be "Waiting for approval"
    And I should see "Sent for approval"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Fail to send a non existing product draft for approval
    Then I should not see "Send for approval"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-3980
  Scenario: Successfully restore the product draft status when I modify it after sending it for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    And I change the "Name" to "Extra large baggy"
    And I save the product
    Then its status should be "In progress"
