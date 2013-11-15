@javascript
Feature: Browse attributes
  In order to check whether an attribute is available in the catalog
  As a user
  I need to be able to see attributes in the catalog

  Scenario: Successfully display attributes
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    When I am on the attributes page
    Then the grid should contain 12 elements
    And I should see the columns Code, Label, Type, Scopable, Localizable and Group
    And I should see attributes sku, name, manufacturer, weather_conditions, description, price, rating, side_view, top_view, size, color and lace_color
