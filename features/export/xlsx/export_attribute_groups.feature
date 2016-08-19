@javascript
Feature: Export attribute groups
  In order to be able to access and modify attribute groups data outside PIM
  As an administrator
  I need to be able to export attribute groups in xlsx format

  Scenario: Successfully export attribute groups in xlsx with headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_attribute_group_export" configuration:
      | filePath | %tmp%/xlsx_footwear_attribute_group_export/xlsx_footwear_attribute_group_export.xlsx |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_attribute_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_attribute_group_export" job to finish
    Then exported xlsx file of "xlsx_footwear_attribute_group_export" should contain:
      | code      | label-en_US         | attributes                                                                                                   | sort_order |
      | info      | Product information | description,length,manufacturer,name,sku,volume,weather_conditions,weight                                    | 1 |
      | marketing | Marketing           | price,rate_sale,rating                                                                                       | 2 |
      | sizes     | Sizes               | size                                                                                                         | 3 |
      | colors    | Colors              | color,lace_color                                                                                             | 4 |
      | media     | Media               | side_view,top_view                                                                                           | 5 |
      | other     | Other               | 123,cap_color,comment,destocking_date,handmade,heel_color,lace_fabric,number_in_stock,sole_color,sole_fabric | 100 |

  Scenario: Successfully export attribute groups in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_attribute_group_export" configuration:
      | filePath    | %tmp%/xlsx_footwear_attribute_group_export/xlsx_footwear_attribute_group_export.xlsx |
      | withHeader  | no                                                                   |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_attribute_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_attribute_group_export" job to finish
    Then exported xlsx file of "xlsx_footwear_attribute_group_export" should contain:
      | info      | 1   | description,length,manufacturer,name,sku,volume,weather_conditions,weight                                    | Product information |
      | marketing | 2   | price,rate_sale,rating                                                                                       | Marketing           |
      | sizes     | 3   | size                                                                                                         | Sizes               |
      | colors    | 4   | color,lace_color                                                                                             | Colors              |
      | media     | 5   | side_view,top_view                                                                                           | Media               |
      | other     | 100 | 123,cap_color,comment,destocking_date,handmade,heel_color,lace_fabric,number_in_stock,sole_color,sole_fabric | Other               |
