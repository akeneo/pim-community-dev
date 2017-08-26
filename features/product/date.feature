@javascript
Feature: Check that imported date is properly displayed
  In order to display date information
  As a product manager
  I need to have dates properly displayed

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And the following attributes:
      | label-en_US | type             | localizable | scopable | useable_as_grid_filter | group | code    |
      | release     | pim_catalog_date | 0           | 0        | 1                      | other | release |
    And the following CSV file to import:
      """
      sku;release
      postit;2014-05-01
      """
    And the following job "csv_default_product_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_default_product_import" import job page
    And I launch the import job
    And I wait for the "csv_default_product_import" job to finish

  Scenario: Successfully display a date in the grid (PIM-2971)
    Given I am on the products page
    And I display the columns SKU, Family, Release, Complete, Created at and Updated at
    Then the row "postit" should contain:
     | column  | value      |
     | release | 05/01/2014 |

  Scenario: Successfully display a date in the product edit form (PIM-2971)
    Given I am on the "postit" product page
    Then the field release should contain "05/01/2014"

  Scenario: Do not change date in history if the date has not been changed in the product (PIM-3009)
    Given I am on the "postit" product page
    And I fill in the following information:
        | SKU | nice_postit |
    And I press the "Save" button
    When I visit the "History" column tab
    Then I should see history:
      | version | property | before | value       |
      | 2       | SKU      | postit | nice_postit |
      | 1       | SKU      |        | postit      |
      | 1       | release  |        | 05/01/2014  |
      | 1       | enabled  |        | 1           |
