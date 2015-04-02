@javascript
Feature: Send a product draft with reference data for approval
  In order to apply my product draft
  As a contributor
  I need to be able to send my product draft for approval

  Background:
    Given a "footwear" catalog configuration
    And the product:
      | sku        | red-louboutin  |
      | categories | winter_boots   |
    And the following "sole_fabric" attribute reference data: PVC, Nylon, Neoprene, Lace, Rubber, Leather
    And the following "sole_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And I am logged in as "Mary"
    And I edit the "red-louboutin" product

  Scenario: Successfully create a new product draft with simple select reference data
    Given I add available attribute Sole color
    And I visit the "Other" group
    Then I fill in the following information:
      | Sole color | Red |
    And I save the product
    Then its status should be "In progress"

  Scenario: Successfully send my product draft ith simple select reference data for approval
    Given I add available attribute Sole color
    And I visit the "Other" group
    Then I fill in the following information:
      | Sole color | Red |
    And I save the product
    And I press the "Send for approval" button
    Then its status should be "Waiting for approval"
    And I should see "Sent for approval"

  Scenario: Successfully restore the product draft status when I modify a simple select after sending it for approval
    Given I add available attribute Sole color
    And I visit the "Other" group
    Then I fill in the following information:
      | Sole color | Red |
    And I save the product
    And I press the "Send for approval" button
    Then I fill in the following information:
      | Sole color  | Blue |
    And I save the product
    Then its status should be "In progress"

  Scenario: Successfully create a new product draft with multi select reference data
    Given I add available attribute Sole fabric
    And I visit the "Other" group
    Then I fill in the following information:
      | Sole fabric | Leather,Neoprene |
    And I save the product
    Then its status should be "In progress"

  Scenario: Successfully send my product draft ith simple multi select reference data for approval
    Given I add available attribute Sole fabric
    And I visit the "Other" group
    Then I fill in the following information:
      | Sole fabric | Leather,Neoprene |
    And I save the product
    And I press the "Send for approval" button
    Then its status should be "Waiting for approval"
    And I should see "Sent for approval"

  Scenario: Successfully restore the product draft status when I modify a multi select after sending it for approval
    Given I add available attribute Sole fabric
    And I visit the "Other" group
    Then I fill in the following information:
      | Sole fabric | Leather,Neoprene |
    And I save the product
    And I press the "Send for approval" button
    Then I fill in the following information:
      | Sole fabric | Leather,PVC |
    And I save the product
    Then its status should be "In progress"
