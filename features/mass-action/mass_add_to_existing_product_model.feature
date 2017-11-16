@javascript
Feature: Apply a add to products to existing product model
  In order to link my products to product model
  As a product manager
  I need to be able to select products and link them to an existing product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page
    And I select rows 1111111171
    And I press the "Bulk actions" button
    And I choose the "Add to an existing product model" operation

  Scenario: It automatically selects family variant when there is only one
    When I fill in the following information:
      | Choose a family | Accessories |
    Then I should see the text "Accessories by size"

  Scenario: Successfully display leaf product models
    When I fill in the following information:
      | Choose a family        | Clothing                   |
      | Choose a variant       | Clothing by color and size |
      | Choose a product model | Apollon blue               |
    When I move on to the next step
    Then I should see the text "Apollon blue"
    And the fields family should be disabled
