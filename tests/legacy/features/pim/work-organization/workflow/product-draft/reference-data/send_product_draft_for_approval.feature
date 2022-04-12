@javascript @proposal-feature-enabled
Feature: Send a product draft with reference data for approval
  In order to apply my product draft
  As a contributor
  I need to be able to send my product draft for approval

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | hoodies    |
      | categories | winter_top |
      | sku        | my-jean    |
    And I am logged in as "Mary"
    And I edit the "my-jean" product

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully send my product draft with simple and multiple select reference data for approval
    Given I visit the "Other" group
    Then I fill in the following information:
      | Lace color    | UA Red           |
      | Sleeve fabric | Tweed, Haircloth |
    And I save the product
    Then its status should be "In progress"
    And I press the Send for approval button
    Then I should see the text "Sent for approval"
    And its status should be "Waiting for approval"
    When I fill in the following information:
      | Lace color    | Tufts Blue |
      | Sleeve fabric | Tricotknit |
    And I save the product
    Then I should see the text "Send for approval"
    And its status should be "In progress"
