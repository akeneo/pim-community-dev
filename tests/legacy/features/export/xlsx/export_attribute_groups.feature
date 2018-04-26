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
      | code      | label-en_US | attributes                                                                                                   | sort_order          |
      | info      | 1           | sku,name,manufacturer,weather_conditions,description,length,volume,weight                                    | Product information |
      | marketing | 2           | price,rating,rate_sale                                                                                       | Marketing           |
      | sizes     | 3           | size                                                                                                         | Sizes               |
      | colors    | 4           | color,lace_color                                                                                             | Colors              |
      | media     | 5           | side_view,top_view,rear_view                                                                                 | Media               |
      | other     | 100         | comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123 | Other               |

  Scenario: Successfully export attribute groups in xlsx without headers:
    Given an "footwear" catalog configuration
    And the following job "xlsx_footwear_attribute_group_export" configuration:
      | filePath   | %tmp%/xlsx_footwear_attribute_group_export/xlsx_footwear_attribute_group_export.xlsx |
      | withHeader | no                                                                                   |
    And I am logged in as "Julia"
    When I am on the "xlsx_footwear_attribute_group_export" export job page
    And I launch the export job
    And I wait for the "xlsx_footwear_attribute_group_export" job to finish
    Then exported xlsx file of "xlsx_footwear_attribute_group_export" should contain:
      | info      | 1   | sku,name,manufacturer,weather_conditions,description,length,volume,weight                                    | Product information |
      | marketing | 2   | price,rating,rate_sale                                                                                       | Marketing           |
      | sizes     | 3   | size                                                                                                         | Sizes               |
      | colors    | 4   | color,lace_color                                                                                             | Colors              |
      | media     | 5   | side_view,top_view,rear_view                                                                                 | Media               |
      | other     | 100 | comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123 | Other               |
