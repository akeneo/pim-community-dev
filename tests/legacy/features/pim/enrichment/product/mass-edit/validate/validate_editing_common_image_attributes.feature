@javascript
Feature: Validate editing common image attributes of multiple products
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

  Scenario: Successfully mass edit an image attribute
    Given I select rows boots and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attribute values" operation
    And I display the Side view attribute
    And I attach file "SNKRS-1R.png" to "Side view"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the file "side_view" of products boots and sneakers should be "SNKRS-1R.png"
    When I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attribute values" operation
    And I display the Side view attribute
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    And the product "boots" should not have the following values:
      | side_view |
    And the product "sandals" should not have the following values:
      | side_view |
    And the product "sneakers" should not have the following values:
      | side_view |
    When I am on the products grid
    And I select rows boots, sandals and sneakers
    And I press the "Bulk actions" button
    And I choose the "Edit attribute values" operation
    And I display the Side view attribute
    And I attach file "bic-core-148.gif" to "Side view"
    And I move on to the next step
    Then I should see validation tooltip "The gif file extension is not allowed for the side_view attribute. Allowed extensions are png, jpeg, jpg."
    And the product "boots" should not have the following values:
      | side_view |
    And the product "sandals" should not have the following values:
      | side_view |
    And the product "sneakers" should not have the following values:
      | side_view |
