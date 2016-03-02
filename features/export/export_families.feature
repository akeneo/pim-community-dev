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
      code;label-en_US;attributes;attribute_as_label;requirements-mobile;requirements-tablet
      boots;Boots;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;name;sku,name,price,size,color;sku,name,description,weather_conditions,price,rating,side_view,size,color
      heels;Heels;sku,name,manufacturer,description,price,side_view,top_view,size,color,heel_color,sole_color,sole_fabric;name;sku,name,price,size,color,heel_color,sole_color;sku,name,description,price,side_view,size,color,heel_color,sole_color
      sneakers;Sneakers;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;name;sku,name,price,size,color;sku,name,description,weather_conditions,price,rating,side_view,size,color
      sandals;Sandals;sku,name,manufacturer,description,price,rating,side_view,size,color;name;sku,name,price,size,color;sku,name,description,price,rating,side_view,size,color
      """

  @javascript
  Scenario: Successfully export families
    Given a "footwear" catalog configuration
    And the following family:
      | code      | label-en_US |requirements-tablet | requirements-mobile |
      | tractors  |             |sku                 | sku                 |
    And the following job "footwear_family_export" configuration:
      | filePath | %tmp%/family_export/family.csv |
    And I am logged in as "Julia"
    And I am on the "footwear_family_export" export job page
    When I launch the export job
    And I wait for the "footwear_family_export" job to finish
    Then I should see "Read 5"
    And I should see "Written 5"
    And exported file of "footwear_family_export" should contain:
      """
      code;label-en_US;attributes;attribute_as_label;requirements-mobile;requirements-tablet
      boots;Boots;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;name;sku,name,price,size,color;sku,name,description,weather_conditions,price,rating,side_view,size,color
      heels;Heels;sku,name,manufacturer,description,price,side_view,top_view,size,color,heel_color,sole_color,sole_fabric;name;sku,name,price,size,color,heel_color,sole_color;sku,name,description,price,side_view,size,color,heel_color,sole_color
      sneakers;Sneakers;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;name;sku,name,price,size,color;sku,name,description,weather_conditions,price,rating,side_view,size,color
      sandals;Sandals;sku,name,manufacturer,description,price,rating,side_view,size,color;name;sku,name,price,size,color;sku,name,description,price,rating,side_view,size,color
      tractors;;sku;sku;sku;sku
      """
