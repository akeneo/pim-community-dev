@javascript
Feature: Display product attributes in the grid
  In order to easily see product data in the grid
  As a regular user
  I need to be able to display values for different attributes in the grid

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
