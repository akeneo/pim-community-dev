@javascript
Feature: View attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes page

  @ce
  Scenario: Successfully view attributes
    Then the grid should contain 25 elements
    And I should see the columns Label, Type, Group, Scopable, Localizable
    And I should see attributes SKU, Name, Manufacturer, Volume, Description, Price, Rating, Side view, Top view, Size, Color, Lace color, Length, Number in stock, Heel color, Sole color, Sole fabric, Lace fabric, Cap color, Rate of sale, Rear view and Attribute 123
    And the rows should be sorted ascending by Label
