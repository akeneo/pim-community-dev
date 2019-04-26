@javascript
Feature: Validate editing common date attributes of multiple products
  In order to update multiple products with valid data
  As a product manager
  I need values to be validated when editing common attributes of products

  # what's tested here?
  # -----------------------------|-------------|---------------|-------------
  # TYPE                         | VALID VALID | INVALID VALUE | NULL VALUE
  # -----------------------------|-------------|---------------|-------------
  # pim_catalog_boolean          | done        | N/A           | N/A
  # pim_catalog_date             | done        | done          | done
  # pim_catalog_file             | done        | done          | done
  # pim_catalog_identifier       | N/A         | N/A           | N/A
  # pim_catalog_image            | done        | done          | done
  # pim_catalog_metric           | done        | done          | done
  # pim_catalog_multiselect      | done        | N/A           | done
  # pim_catalog_number           | done        | done          | done
  # pim_catalog_price_collection | done        | done          | done
  # pim_catalog_simpleselect     | done        | N/A           | done
  # pim_catalog_text             | done        | done          | done
  # pim_catalog_textarea         | done        | done          | done

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                 | allowed_extensions | date_min   | max_characters | group |
      | available | Available   | pim_catalog_boolean  |                    |            |                | other |
      | date      | Date        | pim_catalog_date     |                    | 2014-01-01 |                | other |
      | file      | File        | pim_catalog_file     | txt                |            |                | other |
      | info      | Info        | pim_catalog_textarea |                    |            | 25             | other |
    And the following family:
      | code          | attributes                                                                                                  |
      | master_family | sku,side_view,length,weather_conditions,number_in_stock,price,manufacturer,comment,available,date,file,info |
    And the following products:
      | sku      | family        |
      | boots    | master_family |
      | sneakers | master_family |
      | sandals  | master_family |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully mass edit a date attribute
    Given I select rows boots and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Date attribute
    And I change the Date to "01/01/2015"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then attribute Date of "boots" should be "2015-01-01"
    And attribute Date of "sneakers" should be "2015-01-01"
    When I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Date attribute
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    And the product "boots" should not have the following values:
      | Date |
    And the product "sandals" should not have the following values:
      | Date |
    And the product "sneakers" should not have the following values:
      | Date |
    When I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attributes values" operation
    And I display the Date attribute
    And I change the Date to "01/01/2013"
    And I move on to the next step
    Then I should see validation tooltip "This date should be 2014-01-01 or after."
    And the product "boots" should not have the following values:
      | Date |
    And the product "sandals" should not have the following values:
      | Date |
    And the product "sneakers" should not have the following values:
      | Date |
