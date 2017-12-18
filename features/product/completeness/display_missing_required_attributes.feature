@javascript
Feature: Display the missing required attributes
  In order to ease the enrichment of products
  As a product manager
  I need to be able to display the missing required attributes

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"

  Scenario: Display missing required attributes on products and product models
    When I am on the "apollon" product model page
    Then I should see the text "1 missing required attribute"
    When I remove the "Model picture" file
    And I save the product model
    Then I should see the text "2 missing required attributes"
    When I am on the "apollon_pink" product model page
    Then I should see the text "5 missing required attributes"
    When I am on the "medias" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | IT support |
      | Allowed to edit attributes | IT support |
    And I save the attribute group
    When I am on the "apollon_pink" product model page
    Then I should not see the text "5 missing required attributes"
    But I should see the text "3 missing required attributes"
