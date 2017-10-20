@javascript
Feature: Add children to product model
  In order to enrich the catalog
  As a regular user
  I need to be able to add children to a product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully add a sub product model with one axis to a root product model
    Given I am on the "apollon" product model page
    When I open the variant navigation children selector for level 1
    And I add a new child
    Then I should see the text "Add a color"
    When I fill in "color" with "black"
    And I fill in "code" with "apollon_black"
    And I confirm the child creation
    Then I should see the text "Product model successfully added to the product model"
    And I should be on the product model "apollon_black" edit page

  Scenario: Successfully add a sub product model with many axes to a root product model
    # Here we need to first create everything, including the family variant
    # Create 5 axis (the max), with the 4 accepted attribute types, and set the values

  Scenario: Successfully add a sub product model when I am on a variant product

  Scenario: Successfully add a variant product to a root product model
    # shoes on the variant size (1 level)

  Scenario: Successfully add a variant product to a sub product model
    # t-shirts on the variant size (2 levels)

  Scenario: Successfully add a variant product to a product model with metric as variant axis
    # tvs on the variant display diagonal (1 levels with metric)

  Scenario: Successfully add a variant product with many axes to a root product model

  Scenario: I cannot add a variant product to a root product model when there is two levels of variations
    When I am on the "apollon" product model page
    Then I should not see the variant navigation children selector for level 2

  Scenario: I cannot add a sub product model without code

  Scenario: I cannot add a variant product without code

  Scenario: I cannot add a sub product model without axis value

  Scenario: I cannot add a variant product without axis value

  Scenario: I cannot add a sub product model with an already existing axis value combination

  Scenario: I cannot add a variant product with an already existing axis value combination
