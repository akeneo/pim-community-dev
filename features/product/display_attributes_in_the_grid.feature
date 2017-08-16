@javascript
Feature: Display product attributes in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different attributes in the grid

  Scenario: Successfully display values for simple and multi select attributes
    Given the "default" catalog configuration
    And the following attributes:
      | code               | type                     | label-en_US        | group | useable_as_grid_filter |
      | manufacturer       | pim_catalog_simpleselect | Manufacturer       | other | 1                      |
      | weather_conditions | pim_catalog_multiselect  | Weather conditions | other | 1                      |
    And the following "manufacturer" attribute options: Converse, adidas and lacoste
    And the following "weather_conditions" attribute options: dry, hot, cloudy and stormy
    And the following products:
      | sku      | manufacturer | weather_conditions  |
      | sneakers | Converse     | dry, cloudy, stormy |
      | sandals  | adidas       | dry, hot            |
      | boots    | lacoste      | cloudy              |
    And I am logged in as "Mary"
    And I am on the products grid
    When I display the columns SKU, Manufacturer and Weather conditions
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
    Given the "default" catalog configuration
    And the following attributes:
      | code      | type              | label-en_US | allowed_extensions | useable_as_grid_filter | group |
      | side_view | pim_catalog_image | Side view   | gif,png,jpeg,jpg   | 1                      | other |
      | top_view  | pim_catalog_image | Top view    | gif,png,jpeg,jpg   | 1                      | other |
    And the following products:
      | sku      | side_view             | top_view               |
      | sneakers | %fixtures%/akeneo.jpg |                        |
      | sandals  |                       | %fixtures%/akeneo2.jpg |
    And I am logged in as "Mary"
    And I am on the products grid
    When I display the columns SKU, Side view and Top view
    Then the row "sneakers" should contain the images:
      | column    | title      |
      | Side view | akeneo.jpg |
      | Top view  | **empty**  |
    And the row "sandals" should contain the images:
      | column    | title       |
      | Side view | **empty**   |
      | Top view  | akeneo2.jpg |
