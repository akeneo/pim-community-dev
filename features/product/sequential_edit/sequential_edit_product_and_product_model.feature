@javascript
Feature: Edit sequentially some products
  In order to enrich the catalog
  As a regular user
  I need to be able to edit sequentially some products

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products page

  @ce
  Scenario: Successfully sequentially edit some products but not the product models 1/2
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I sort by "ID" value ascending
    And I select rows model-tshirt-divided-crimson-red, running-shoes-xxs-crimson-red
    When I press the "Sequential edit" button
    Then I should see the text "Divided crimson red"
    And I should see the text "Save and next"

  Scenario: Successfully sequentially edit some product models
    Given I show the filter "color"
    And I filter by "color" with operator "in list" and value "Crimson red"
    And I sort by "ID" value ascending
    And I select rows model-tshirt-divided-crimson-red, model-tshirt-unique-color-kurt
    When I press the "Sequential edit" button
    Then I should see the text "Divided crimson red"
    And I should see the text "Save and next"
    And I press the "Save and next" button
    And I should see the text "Model-tshirt-unique-color-kurt"
    And I should see the text "Save and finish"
