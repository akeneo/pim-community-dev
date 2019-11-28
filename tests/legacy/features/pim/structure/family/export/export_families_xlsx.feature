@javascript
Feature: Export families in XLSX
  In order to be able to access and modify attributes data outside PIM
  As a product manager
  I need to be able to export families in XLSX

  Background:
    Given an "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully export families
    Given the following family:
      | code     | label-en_US | requirements-tablet | requirements-mobile |
      | tractors |             | sku                 | sku                 |
    And the following job "xlsx_footwear_family_export" configuration:
      | filePath | %tmp%/family_export/family.xlsx |
    And I am on the "xlsx_footwear_family_export" export job page
    When I launch the export job
    And I wait for the "xlsx_footwear_family_export" job to finish
    Then I should see the text "Read 6"
    And I should see the text "Written 6"
    And exported xlsx file of "xlsx_footwear_family_export" should contain:
      | code     | label-en_US | attributes                                                                                                 | attribute_as_label | requirements-mobile                             | requirements-tablet                                                       | attribute_as_image |
      | boots    | Boots       | color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions | name               | color,name,price,size,sku                       | color,description,name,price,rating,side_view,size,sku,weather_conditions |                    |
      | heels    | Heels       | color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view    | name               | color,heel_color,name,price,size,sku,sole_color | color,description,heel_color,name,price,side_view,size,sku,sole_color     |                    |
      | sneakers | Sneakers    | color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions | name               | color,name,price,size,sku                       | color,description,name,price,rating,side_view,size,sku,weather_conditions |                    |
      | sandals  | Sandals     | color,description,manufacturer,name,price,rating,side_view,size,sku                                        | name               | color,name,price,size,sku                       | color,description,name,price,rating,side_view,size,sku                    | side_view          |
      | led_tvs  | LED TVs     | color,description,manufacturer,name,price,rating,side_view,size,sku                                        | name               | color,name,price,size,sku                       | color,description,name,price,rating,side_view,size,sku                    |                    |
      | tractors |             | sku                                                                                                        | sku                | sku                                             | sku                                                                       |                    |
