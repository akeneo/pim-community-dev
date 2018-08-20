@javascript
Feature: Export families
  In order to be able to access and modify families outside PIM
  As a product manager
  I need to be able to export families in CSV

  Scenario: Successfully export catalog families
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_family_export" configuration:
      | filePath | %tmp%/family_export/family.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_family_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_family_export" job to finish
    Then I should see the text "Read 5"
    And I should see the text "Written 5"
    And exported file of "csv_footwear_family_export" should contain:
      """
      code;label-en_US;attributes;attribute_as_label;requirements-mobile;requirements-tablet;attribute_as_image
      boots;Boots;color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku,weather_conditions;
      heels;Heels;color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view;name;color,heel_color,name,price,size,sku,sole_color;color,description,heel_color,name,price,side_view,size,sku,sole_color;
      sneakers;Sneakers;color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku,weather_conditions;
      sandals;Sandals;color,description,manufacturer,name,price,rating,side_view,size,sku;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku;side_view
      led_tvs;"LED TVs";color,description,manufacturer,name,price,rating,side_view,size,sku;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku;
      """

  Scenario: Successfully export families CSV
    Given a "footwear" catalog configuration
    And the following family:
      | code     | label-en_US | requirements-tablet | requirements-mobile |
      | tractors |             | sku                 | sku                 |
    And the following job "csv_footwear_family_export" configuration:
      | filePath | %tmp%/family_export/family.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_family_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_family_export" job to finish
    Then I should see the text "Read 6"
    And I should see the text "Written 6"
    And exported file of "csv_footwear_family_export" should contain:
      """
      code;label-en_US;attributes;attribute_as_label;requirements-mobile;requirements-tablet;attribute_as_image
      boots;Boots;color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku,weather_conditions;
      heels;Heels;color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view;name;color,heel_color,name,price,size,sku,sole_color;color,description,heel_color,name,price,side_view,size,sku,sole_color;
      sneakers;Sneakers;color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku,weather_conditions;
      sandals;Sandals;color,description,manufacturer,name,price,rating,side_view,size,sku;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku;side_view
      led_tvs;"LED TVs";color,description,manufacturer,name,price,rating,side_view,size,sku;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku;
      tractors;;sku;sku;sku;sku
      """

  @ce @jira https://akeneo.atlassian.net/browse/SDS-511
  Scenario: Successfully export families after activating new locales
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_family_export" configuration:
      | filePath | %tmp%/family_export/family.csv |
    And I am logged in as "Julia"
    And I add the "fr_BE" locale to the "tablet" channel
    And I add the "fr_Ca" locale to the "tablet" channel
    And I add the "fr_CH" locale to the "tablet" channel
    And I add the "fr_LU" locale to the "tablet" channel
    And I add the "fr_MC" locale to the "tablet" channel
    And I add the "en_AU" locale to the "tablet" channel
    And I add the "en_CA" locale to the "tablet" channel
    And I am on the "Sneakers" family page
    And I fill in the following information:
      | English (Australia)  | Sneakers |
      | English (Canada)     | Sneakers |
      | French (Belgium)     | Baskets  |
      | French (Canada)      | Baskets  |
      | French (Switzerland) | Baskets  |
      | French (Luxembourg)  | Baskets  |
      | French (Monaco)      | Baskets  |
    And I save the family
    And I should not see the text "There are unsaved changes."
    And I am on the "csv_footwear_family_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_family_export" job to finish
    Then I should see the text "Read 5"
    And I should see the text "Written 5"
    And exported file of "csv_footwear_family_export" should contain:
      """
      code;label-fr_CH;label-fr_LU;label-fr_MC;label-fr_CA;label-fr_BE;label-en_US;label-en_AU;label-en_CA;attributes;attribute_as_label;requirements-mobile;requirements-tablet;attribute_as_image
      boots;;;;;;Boots;;;color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku,weather_conditions;
      heels;;;;;;Heels;;;color,description,heel_color,manufacturer,name,price,side_view,size,sku,sole_color,sole_fabric,top_view;name;color,heel_color,name,price,size,sku,sole_color;color,description,heel_color,name,price,side_view,size,sku,sole_color;
      sneakers;Baskets;Baskets;Baskets;Baskets;Baskets;Sneakers;Sneakers;Sneakers;color,description,lace_color,manufacturer,name,price,rating,side_view,size,sku,top_view,weather_conditions;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku,weather_conditions;
      sandals;;;;;;Sandals;;;color,description,manufacturer,name,price,rating,side_view,size,sku;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku;side_view
      led_tvs;;;;;;"LED TVs";;;color,description,manufacturer,name,price,rating,side_view,size,sku;name;color,name,price,size,sku;color,description,name,price,rating,side_view,size,sku;
      """
