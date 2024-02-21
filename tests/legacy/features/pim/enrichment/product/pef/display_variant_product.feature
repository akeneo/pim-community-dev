@javascript
Feature: Display a variant product
  In order to enrich the catalog
  As a regular user
  I need to be able display a variant product with their specific information

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Mary"

  @critical
  Scenario: I can see the variant meta only in variant products
    When I am on the "1111111111" product page
    Then I should see the text "VARIANT"
    And I should see the text "Clothing by color/size"
    When I am on the "watch" product page
    Then I should not see the text "VARIANT"
