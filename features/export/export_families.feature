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
    code;attributes;attribute_as_label;requirements-mobile;requirements-tablet;label-en_US
    boots;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;name;color;color;Boots
    heels;sku,name,manufacturer,description,price,side_view,top_view,size,color,heel_color,sole_color,sole_fabric;name;sole_color;sole_color;Heels
    sneakers;sku,name,manufacturer,weather_conditions,description,price,rating,side_view,top_view,size,color,lace_color;name;color;color;Sneakers
    sandals;sku,name,manufacturer,description,price,rating,side_view,size,color;name;color;color;Sandals
    """
