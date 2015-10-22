Feature: Export families
  In order to be able to access and modify families outside PIM
  As a product manager
  I need to be able to export families

  @javascript
  Scenario: Successfully export families
    Given a "footwear" catalog configuration
    And the following job "footwear_family_export" configuration:
      | filePath | %tmp%/family_export/family.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_family_export" export job page
    When I launch the export job
    And I wait for the "footwear_family_export" job to finish
    Then I should see "Read 4"
    And I should see "Written 4"
    And exported file of "footwear_family_export" should contain:
      """
      code;attributes;label-en_US;attribute_as_label;requirements-mobile;requirements-tablet
      boots;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;Boots;name;sku,name,price,size,color;sku,name,description,weather_conditions,price,rating,side_view,size,color
      heels;sku,name,manufacturer,description,price,side_view,top_view,size,color,heel_color,sole_color,sole_fabric;Heels;name;sku,name,price,size,color,heel_color,sole_color;sku,name,description,price,side_view,size,color,heel_color,sole_color
      sneakers;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;Sneakers;name;sku,name,price,size,color;sku,name,description,weather_conditions,price,rating,side_view,size,color
      sandals;sku,name,manufacturer,description,price,rating,side_view,size,color;Sandals;name;sku,name,price,size,color;sku,name,description,price,rating,side_view,size,color
      """
