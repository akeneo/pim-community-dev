@javascript
Feature: View attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  Scenario: Successfully view attributes
    Then the grid should contain 26 elements
    And I should see the columns Code, Label, Type, Scopable, Localizable and Group
    And I should see attributes sku, name, manufacturer, volume, weather_conditions, description, price, rating, side_view, top_view, size, color, lace_color, length, number_in_stock, heel_color, sole_color, sole_fabric, lace_fabric, cap_color, rate_sale, weight and 123
    And the rows should be sorted ascending by Code
