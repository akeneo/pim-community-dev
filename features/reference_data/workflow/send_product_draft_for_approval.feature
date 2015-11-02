@javascript
Feature: Send a product draft with reference data for approval
  In order to apply my product draft
  As a contributor
  I need to be able to send my product draft for approval

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family     | pants      |
      | categories | winter_top |
      | sku        | my-jean    |
    And the following "sleeve_fabric" attribute reference data: PVC, Nylon, Neoprene, Lace, Rubber, Leather
    And the following "lace_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And I am logged in as "Mary"
    And I edit the "my-jean" product

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully create a new product draft with simple select reference data
    Given I add available attribute Lace color
    And I visit the "Other" group
    Then I fill in the following information:
      | Lace color | Red |
    And I save the product
    Then its status should be "In progress"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully send my product draft ith simple select reference data for approval
    Given I add available attribute Lace color
    And I visit the "Other" group
    Then I fill in the following information:
      | Lace color | Red |
    And I save the product
    And I press the Send for approval button
    Then its status should be "Waiting for approval"
    And I should see "Sent for approval"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully restore the product draft status when I modify a simple select after sending it for approval
    Given I add available attribute Lace color
    And I visit the "Other" group
    Then I fill in the following information:
      | Lace color | Red |
    And I save the product
    And I press the Send for approval button
    Then I fill in the following information:
      | Lace color  | Blue |
    And I save the product
    Then its status should be "In progress"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully create a new product draft with multi select reference data
    Given I add available attribute Sleeve fabric
    And I visit the "Other" group
    Then I fill in the following information:
      | Sleeve fabric | Leather, Neoprene |
    And I save the product
    Then its status should be "In progress"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully send my product draft ith simple multi select reference data for approval
    Given I add available attribute Sleeve fabric
    And I visit the "Other" group
    Then I fill in the following information:
      | Sleeve fabric | Leather, Neoprene |
    And I save the product
    And I press the Send for approval button
    Then its status should be "Waiting for approval"
    And I should see "Sent for approval"

  @jira https://akeneo.atlassian.net/browse/PIM-4597
  Scenario: Successfully restore the product draft status when I modify a multi select after sending it for approval
    Given I add available attribute Sleeve fabric
    And I visit the "Other" group
    Then I fill in the following information:
      | Sleeve fabric | Leather, Neoprene |
    And I save the product
    And I press the Send for approval button
    Then I fill in the following information:
      | Sleeve fabric | Leather, PVC |
    And I save the product
    Then its status should be "In progress"
