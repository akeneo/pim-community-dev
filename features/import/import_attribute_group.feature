@javascript
Feature: Import attribute groups
  In order to setup my application
  As an administrator
  I need to be able to import attribute groups

  Scenario: Successfully import new attribute group in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;6
      """
    And the following job "csv_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_group_import" job to finish
    Then there should be the following attribute groups:
      | code          | label-en_US   | attributes                           | sort_order |
      | manufacturing | Manufacturing | lace_fabric,manufacturer,sole_fabric | 6          |

  Scenario: Successfully update existing attribute group and add a new one
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;6
      sizes;Size;size;3
      marketing;Marketing;sku;10
      other;Default;sku;200
      """
    And the following job "csv_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_attribute_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_attribute_group_import" job to finish
    Then there should be the following attribute groups:
      | code          | label-en_US   | attributes                                                                                                  | sort_order |
      | manufacturing | Manufacturing | lace_fabric,manufacturer,sole_fabric                                                                        | 6          |
      | sizes         | Size          | size                                                                                                        | 3          |
      | marketing     | Marketing     | sku                                                                                                         | 10         |
      | other         | Default       | 123,cap_color,comment,destocking_date,handmade,heel_color,number_in_stock,price,rate_sale,rating,sole_color | 200        |
