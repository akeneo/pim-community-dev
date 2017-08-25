@javascript
Feature: Edit a product model
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product model

  Background:
    Given a "catalog_modeling" catalog configuration

  Scenario: Successfully display family variant name of a product model
    Given I am logged in as "Mary"
    And I edit the "amor" product model
    Then I should see the text "Clothing by color/size"

  Scenario: Successfully edit and save a root product model
    Given I am logged in as "Mary"
    And I edit the "amor" product model
    And I visit the "Marketing" group
    And I fill in the following information:
      | Model name | Heritage jacket navy chilly tiki |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product Model name should be "Heritage jacket navy chilly tiki"

  Scenario: Successfully edit and save a sub product model
    Given I am logged in as "Mary"
    And I edit the "apollon_blue" product model
    And I visit the "Marketing" group
    And I fill in the following information:
      | Name | Apollonito blue |
    When I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the product Name should be "Apollonito blue"
