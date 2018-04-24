@javascript
Feature: Filter attributes
  In order to check whether an attribute is available in the catalog
  As a product manager
  I need to be able to see attributes in the catalog

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the attributes grid

  Scenario Outline: Successfully filter attributes
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter        | operator | value  | result                                                                                                                                                                                                                                                     | count |
      | type          | in list  | Image  | Side view, Rear view and Top view                                                                                                                                                                                                                          | 3     |
      | scopable      |          | yes    | Description, Cap color, Rear view and Lace fabric                                                                                                                                                                                                          | 4     |
      | scopable      |          | no     | Comment, SKU, Volume, Name, Manufacturer, Weather conditions, Price, Rating, Side view, Top view, Size, Color, Lace color, Length, Destocking date, Handmade, Number in stock, Heel color, Sole color, Sole fabric, Rate of sale, Weight and Attribute 123 | 23    |
      | localizable   |          | yes    | Name, Description, Cap color, Rear view and Lace fabric                                                                                                                                                                                                    | 5     |
      | localizable   |          | no     | Comment, Volume, SKU, Manufacturer, Weather conditions, Price, Rating, Side view, Top view, Size, Color, Lace color, Length and Number in stock, Destocking date, Handmade, Heel color, Sole color, Sole fabric, Rate of sale, Weight and Attribute 123    | 22    |
      | group         | in list  | Colors | Color and Lace color                                                                                                                                                                                                                                       | 2     |

  Scenario: Successfully search on label
    When I search "m"
    Then the grid should contain 6 elements
    And I should see entities Comment, Volume, Handmade, Name, Manufacturer and Number in stock
