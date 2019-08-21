@javascript
Feature: Filter attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes grid

  Scenario: Successfully search on label
    When I search "m"
    Then the grid should contain 6 elements
    And I should see entities Comment, Volume, Handmade, Name, Manufacturer and Number in stock
