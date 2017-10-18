@javascript
Feature: Edit product model and filter attributes
  In order to be efficient when enriching product models
  As a regular user
  I need to be able to edit a product model and choose which attributes are displayed

  Background:
    Given the "catalog_modeling" catalog configuration
    And the following root product models:
      | code      | family_variant | collection  |
      | model-nin | clothing_color | summer_2016 |
    And I am logged in as "Julia"
    And I am on the "model-nin" product model page

  Scenario: Edit the product model and show only missing required attributes
    When I filter attributes with "All missing required attributes"
    And I visit the "All" group
    Then I should not see the text "Collection"
    But I should see the text "Model description"
    And I should see the text "Model picture"
    And I should see the text "Size"
    And I should see the text "Wash temperature"
    And I should see the text "Care instructions"
    And I should see the text "Material"
    When I filter attributes with "All attributes"
    Then I should see the text "Collection"

  Scenario: Edit the product model and show only missing required attributes from an attribute group
    When I filter attributes with "All missing required attributes"
    And I visit the "Medias" group
    Then I should not see the text "Variation picture"
    But I should see the text "Model picture"

  Scenario: Edit the product model and show all missing required attributes by clicking on attribute group header
    And I visit the "All" group
    When I filter attributes with "All attributes"
    And I click on the "marketing" required attribute indicator
    Then I should not see the text "Collection"
    But I should see the text "Model description"
    When I filter attributes with "All attributes"
    Then I should see the text "Collection"
    And I should see the text "Model description"

  Scenario: Edit the product model and show only group missing required attributes by clicking on attribute group header
    And I visit the "Marketing" group
    And I click on the "marketing" required attribute indicator
    Then I should not see the text "Collection"
    And I should not see the text "Size"
    But I should see the text "Model description"
    When I filter attributes with "All attributes"
    Then I should see the text "Collection"
    But I should not see the text "Size"
    And I should see the text "Model description"
