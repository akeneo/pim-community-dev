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

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully create a new product draft
    When I change the "Name" to "Baggy"
    And I save the product
    Then its status should be "In progress"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully send my product draft for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the Send for approval button
    Then I should see "Sent for approval"
    And its status should be "Waiting for approval"

  Scenario: Fail to send a non existing product draft for approval
    Then I should not see "Send for approval"

  @unstable @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully restore the product draft status when I modify it after sending it for approval
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the Send for approval button
    And I change the "Name" to "Extra large baggy"
    And I save the product
    Then its status should be "In progress"

  Scenario: Successfully restore the product draft data when I send it for approval with unsaved changes
    Given I change the "Name" to "Baggy"
    And I save the product
    And I should see that Name is a modified value
    And I change the "Name" to "Extra large baggy"
    Then I should see the text "There are unsaved changes."
    When I press the Send for approval button
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to send this draft?                                                                    |
      | content | Unsaved changes will be lost. Are you sure you want to send your draft for approval without unsaved changes? |
    When I confirm the dialog
    Then I should see the flash message "Product draft sent for approval"
    And the product Name should be "Baggy"

  Scenario: Successfully send a product draft for approval with a comment
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    And I fill in this comment in the popin: "This product had the wrong name, I changed it."
    Then I should see that "209" characters are remaining
    And I press the "Send" button in the popin
    Then its status should be "Waiting for approval"
    When I logout
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type | message                                                          | comment                                        |
      | add  | Mary Smith has sent a proposal to review for the product my-pant | This product had the wrong name, I changed it. |
    When I click on the notification "Mary Smith has sent a proposal to review for the product my-pant"
    Then I should be on the proposals index page
    And the grid should contain 1 element
    And I should see the following proposal:
      | product | author | attribute | original | new   |
      | my-pant | Mary   | name      |          | Baggy |

  Scenario: Fail to send a product draft for approval with a comment longer than 255 characters
    When I change the "Name" to "Baggy"
    And I save the product
    And I press the "Send for approval" button
    And I fill in this comment in the popin:
      """
      This product had the wrong name, I changed it. For a moment I hesitated between baggy and potato bag but as I read
      a lot of fashion magazines I can make a difference! You really should read this kind of magazine, it would help
      you better dressed. I should withdraw that comment, I might get fired ...
      """
    Then I should see that "-45" characters are remaining
    And I should not be able to send the comment
