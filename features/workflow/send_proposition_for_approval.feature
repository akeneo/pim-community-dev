@javascript
Feature: Send a proposition for approval
  In order to apply my proposition
  As a contributor
  I need to be able to send my proposition for approval

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | pants      |
      | categories | winter_top |
      | sku        | my-pant    |
    And I am logged in as "Mary"
    And I edit the "my-pant" product

  Scenario: Successfully create a new proposition
    When I change the "Name" to "Baggy"
    And I save the product
    Then its status should be "In progress"

  Scenario: Successfully send my proposition for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    Then its status should be "Waiting for approval"
    And I should see "Sent for approval"

  Scenario: Fail to send a non existing proposition for approval
    Then I should not see "Send for approval"

  Scenario: Successfully restore the proposition status when I modify it after sending it for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    And I change the "Name" to "Extra large baggy"
    And I save the product
    Then its status should be "In progress"
