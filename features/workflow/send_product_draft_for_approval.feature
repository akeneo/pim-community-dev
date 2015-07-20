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

  Scenario: Fail to send a non existing product draft for approval
    Then I should not see "Send for approval"

  @skip-pef @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully restore the product draft status when I modify it after sending it for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    And I change the "Name" to "Extra large baggy"
    And I save the product
    Then its status should be "In progress"

  Scenario: Successfully restore the product draft data when I send it for approval with unsaved changes
    Given I change the "Name" to "Baggy"
    And I save the product
    And I change the "Name" to "Extra large baggy"
    Then I should see the text "There are unsaved changes."
    When I press the "Send for approval" button
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to send this draft?                                                                    |
      | content | Unsaved changes will be lost. Are you sure you want to send your draft for approval without unsaved changes? |
    When I confirm the dialog
    Then I should see "Sent for approval"
    And the product Name should be "Baggy"
