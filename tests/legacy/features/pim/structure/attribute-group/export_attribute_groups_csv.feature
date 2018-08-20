@javascript
Feature: Export attribute groups
  In order to be able to access and modify attribute groups data outside PIM
  As an administrator
  I need to be able to export attribute groups

  Scenario: Successfully export attribute groups
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_attribute_group_export" configuration:
      | filePath | %tmp%/attribute_group_export/attribute_group_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_attribute_group_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_attribute_group_export" job to finish
    Then I should see the text "Read 6"
    And I should see the text "Written 6"
    And exported file of "csv_footwear_attribute_group_export" should contain:
    """
    code;label-en_US;attributes;sort_order
    info;"Product information";sku,name,manufacturer,weather_conditions,description,length,volume,weight;1
    marketing;Marketing;price,rating,rate_sale;2
    sizes;Sizes;size;3
    colors;Colors;color,lace_color;4
    media;Media;side_view,top_view,rear_view;5
    other;Other;comment,number_in_stock,destocking_date,handmade,heel_color,sole_color,cap_color,sole_fabric,lace_fabric,123;100
    """
