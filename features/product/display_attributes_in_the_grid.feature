@javascript
Feature: Display product attributes in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different attributes in the grid

  Scenario: Successfully display values for simple and multi select attributes
    Given the "default" catalog configuration
    And the following attributes:
      | code               | type         | label-en_US        | group |
      | manufacturer       | simpleselect | Manufacturer       | other |
      | weather_conditions | multiselect  | Weather conditions | other |
    And the following "manufacturer" attribute options: Converse, adidas and lacoste
    And the following "weather_conditions" attribute options: dry, hot, cloudy and stormy
    And the following products:
      | sku      | manufacturer | weather_conditions  |
      | sneakers | Converse     | dry, cloudy, stormy |
      | sandals  | adidas       | dry, hot            |
      | boots    | lacoste      | cloudy              |
    And I am logged in as "Mary"
    And I am on the products page
    When I display the columns sku, manufacturer and weather_conditions
    Then the row "sneakers" should contain:
      | column             | value                     |
      | Manufacturer       | [Converse]                |
      | Weather conditions | [dry], [cloudy], [stormy] |
    And the row "sandals" should contain:
      | column             | value        |
      | Manufacturer       | [adidas]     |
      | Weather conditions | [dry], [hot] |
    And the row "boots" should contain:
      | column             | value     |
      | Manufacturer       | [lacoste] |
      | Weather conditions | [cloudy]  |

  Scenario: Successfully display image attributes
    Given the "footwear" catalog configuration
    And the following products:
      | sku      | side_view             | top_view               |
      | sneakers | %fixtures%/akeneo.jpg |                        |
      | sandals  |                       | %fixtures%/akeneo2.jpg |
    And I am logged in as "Mary"
    And I am on the products page
    When I display the columns sku, side_view and top_view
    Then the row "sneakers" should contain the images:
      | column    | title      |
      | Side view | akeneo.jpg |
      | Top view  | **empty**  |
    And the row "sandals" should contain the images:
      | column    | title       |
      | Side view | **empty**   |
      | Top view  | akeneo2.jpg |
