@javascript
Feature: Export products
  In order to use the enriched product data
  As a product manager
  I need to be able to export the products to several channels

  Scenario: Successfully export products to multiple channels
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
    And the following job "tablet_product_export" configuration:
      | filePath | %tmp%/tablet_product_export/tablet_product_export.csv |
    And the following job "print_product_export" configuration:
      | filePath | %tmp%/print_product_export/print_product_export.csv |
    And the following products:
      | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute   | value                                | locale | scope     |
      | tshirt-white | name        | White t-shirt                        | en_US  |           |
      | tshirt-white | name        | White t-shirt                        | en_GB  |           |
      | tshirt-white | name        | T-shirt blanc                        | fr_FR  |           |
      | tshirt-white | name        | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-white | description | A stylish white t-shirt              | en_US  | ecommerce |
      | tshirt-white | description | An elegant white t-shirt             | en_GB  | ecommerce |
      | tshirt-white | description | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-white | description | Ein elegantes weißes T-Shirt         | de_DE  | ecommerce |
      | tshirt-white | description | A really stylish white t-shirt       | en_US  | print     |
      | tshirt-white | description | Ein sehr elegantes weißes T-Shirt    | de_DE  | print     |
      | tshirt-black | name        | Black t-shirt                        | en_US  |           |
      | tshirt-black | name        | Black t-shirt                        | en_GB  |           |
      | tshirt-black | name        | T-shirt noir                         | fr_FR  |           |
      | tshirt-black | name        | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-black | description | A stylish black t-shirt              | en_US  | ecommerce |
      | tshirt-black | description | An elegant black t-shirt             | en_GB  | ecommerce |
      | tshirt-black | description | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-black | description | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-black | description | A really stylish black t-shirt       | en_US  | print     |
      | tshirt-black | description | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;;;;;"Ein elegantes weißes T-Shirt";"An elegant white t-shirt";"A stylish white t-shirt";"Un T-shirt blanc élégant";1;tshirts;;0;;;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";"White t-shirt";"T-shirt blanc";;10.00;9.00;15.00;;size_M;;;;;
    tshirt-black;;men_2013,men_2014,men_2015;black;;;;usa;;;;;;"Ein elegantes schwarzes T-Shirt";"An elegant black t-shirt";"A stylish black t-shirt";"Un T-shirt noir élégant";1;tshirts;;0;;;;;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";"Black t-shirt";"T-shirt noir";;10.00;9.00;15.00;;size_L;;;;;
    """
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-tablet;datasheet;description-en_GB-tablet;description-en_US-tablet;enabled;family;groups;handmade;image;legend-en_GB;legend-en_US;manufacturer;material;name-en_GB;name-en_US;number_in_stock-tablet;price-EUR;price-GBP;price-USD;release_date-tablet;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;;;;1;tshirts;;0;;;;american_apparel;cotton;"White t-shirt";"White t-shirt";;10.00;9.00;15.00;;size_M;;;;;
    tshirt-black;;men_2013,men_2014,men_2015;black;;;;usa;;;;;1;tshirts;;0;;;;american_apparel;cotton;"Black t-shirt";"Black t-shirt";;10.00;9.00;15.00;;size_L;;;;;
    """
    When I am on the "print_product_export" export job page
    And I launch the export job
    And I wait for the "print_product_export" job to finish
    Then exported file of "print_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-print;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-print;description-en_US-print;enabled;family;groups;handmade;image;legend-de_DE;legend-en_US;manufacturer;material;name-de_DE;name-en_US;number_in_stock-print;price-EUR;price-GBP;price-USD;release_date-print;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;;;;;"Ein sehr elegantes weißes T-Shirt";"A really stylish white t-shirt";1;tshirts;;0;;;;american_apparel;cotton;"Weißes T-Shirt";"White t-shirt";;10.00;9.00;15.00;;size_M;;;;;
    tshirt-black;;men_2013,men_2014,men_2015;black;;;;usa;;;;;;"Ein sehr elegantes schwarzes T-Shirt";"A really stylish black t-shirt";1;tshirts;;0;;;;american_apparel;cotton;"Schwarzes T-Shirt";"Black t-shirt";;10.00;9.00;15.00;;size_L;;;;;
    """

  Scenario: Do not export products that are not classified
    Given an "apparel" catalog configuration
    And the following job "tablet_product_export" configuration:
      | filePath | %tmp%/tablet_product_export/tablet_product_export.csv                                                                                                                                       |
      | filters  | {"structure": {"locales": ["en_US", "en_GB"], "scope": "tablet"},"data":[{"field":"completeness","operator":"=","value":100}, {"field":"categories","operator":"IN","value":["men_2013"]}]} |
    And the following products:
      | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts |                              | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute | value             | locale | scope |
      | tshirt-white | name      | White t-shirt     | en_US  |       |
      | tshirt-white | name      | White t-shirt     | en_GB  |       |
      | tshirt-white | name      | T-shirt blanc     | fr_FR  |       |
      | tshirt-white | name      | Weißes T-Shirt    | de_DE  |       |
      | tshirt-black | name      | Black t-shirt     | en_US  |       |
      | tshirt-black | name      | Black t-shirt     | en_GB  |       |
      | tshirt-black | name      | T-shirt noir      | fr_FR  |       |
      | tshirt-black | name      | Schwarzes T-Shirt | de_DE  |       |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-tablet;datasheet;description-en_GB-tablet;description-en_US-tablet;enabled;family;groups;handmade;image;legend-en_GB;legend-en_US;manufacturer;material;name-en_GB;name-en_US;number_in_stock-tablet;price-EUR;price-GBP;price-USD;release_date-tablet;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;;;;1;tshirts;;0;;;;american_apparel;cotton;"White t-shirt";"White t-shirt";;10.00;9.00;15.00;;size_M;;;;;
    """

  Scenario: Export only attributes with the locale specific
    Given an "apparel" catalog configuration
    And the following job "tablet_product_export" configuration:
      | filePath | %tmp%/tablet_product_export/tablet_product_export.csv                                                                                                                                                       |
      | filters  | {"structure": {"locales": ["en_US", "en_GB"], "scope": "tablet"},"data":[{"field":"completeness","operator":"=","value":100}, {"field":"categories","operator":"IN CHILDREN","value":["2013_collection"]}]} |
    And the following attributes:
      | code                      | type             | localizable | available_locales | group |
      | locale_specific_attribute | pim_catalog_text | 1           | en_US,fr_FR       | other |
    And the following products:
      | sku          | family  | categories                   | price                 | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts |                              | 10 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute                 | value               | locale | scope |
      | tshirt-white | name                      | White t-shirt       | en_US  |       |
      | tshirt-white | name                      | White t-shirt       | en_GB  |       |
      | tshirt-white | name                      | T-shirt blanc       | fr_FR  |       |
      | tshirt-white | name                      | Weißes T-Shirt      | de_DE  |       |
      | tshirt-white | locale_specific_attribute | specific attribute  | fr_FR  |       |
      | tshirt-white | locale_specific_attribute | attribut specifique | en_US  |       |
      | tshirt-black | name                      | Black t-shirt       | en_US  |       |
      | tshirt-black | name                      | Black t-shirt       | en_GB  |       |
      | tshirt-black | name                      | T-shirt noir        | fr_FR  |       |
      | tshirt-black | name                      | Schwarzes T-Shirt   | de_DE  |       |
      | tshirt-black | name                      | Schwarzes T-Shirt   | de_DE  |       |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-tablet;datasheet;description-en_GB-tablet;description-en_US-tablet;enabled;family;groups;handmade;image;legend-en_GB;legend-en_US;locale_specific_attribute-en_US;manufacturer;material;name-en_GB;name-en_US;number_in_stock-tablet;price-EUR;price-GBP;price-USD;release_date-tablet;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;;;;1;tshirts;;0;;;;"attribut specifique";american_apparel;cotton;"White t-shirt";"White t-shirt";;10.00;9.00;15.00;;size_M;;;;;
    """

  @jira https://akeneo.atlassian.net/browse/PIM-4182
  Scenario: Export decimal attributes with the correct decimals formatting
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
    And the following products:
      | sku          | family  | categories                   | price                    | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute       | value                           | locale | scope     |
      | tshirt-white | name            | T-shirt blanc                   | fr_FR  |           |
      | tshirt-white | name            | Weißes T-Shirt                  | de_DE  |           |
      | tshirt-white | description     | Un T-shirt blanc élégant        | fr_FR  | ecommerce |
      | tshirt-white | description     | Ein elegantes weißes T-Shirt    | de_DE  | ecommerce |
      | tshirt-white | number_in_stock | 186                             |        | ecommerce |
      | tshirt-white | customs_tax     | 4.20 EUR, 6 USD, 3.80 GBP       | de_DE  |           |
      | tshirt-black | name            | T-shirt noir                    | fr_FR  |           |
      | tshirt-black | name            | Schwarzes T-Shirt               | de_DE  |           |
      | tshirt-black | description     | Un T-shirt noir élégant         | fr_FR  | ecommerce |
      | tshirt-black | description     | Ein elegantes schwarzes T-Shirt | de_DE  | ecommerce |
      | tshirt-black | number_in_stock | 98                              |        | ecommerce |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;4.20;3.80;6.00;;"Ein elegantes weißes T-Shirt";;;"Un T-shirt blanc élégant";1;tshirts;;0;;;;;;american_apparel;cotton;"Weißes T-Shirt";;;"T-shirt blanc";186;10.90;9.00;15.00;;size_M;;;;;
    tshirt-black;;men_2013,men_2014,men_2015;black;;;;usa;;;;;;"Ein elegantes schwarzes T-Shirt";;;"Un T-shirt noir élégant";1;tshirts;;0;;;;;;american_apparel;cotton;"Schwarzes T-Shirt";;;"T-shirt noir";98;10.90;9.00;15.00;;size_L;;;;;
    """

  @jira https://akeneo.atlassian.net/browse/PIM-4182
  Scenario: Export metric attributes with the correct decimals formatting
    Given an "apparel" catalog configuration
    And the following job "ecommerce_product_export" configuration:
      | filePath | %tmp%/ecommerce_product_export/ecommerce_product_export.csv |
    And the following products:
      | sku          | family  | categories                   | price                    | size   | color | manufacturer     | material | country_of_manufacture |
      | tshirt-white | tshirts | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP | size_M | white | american_apparel | cotton   | usa                    |
      | tshirt-black | tshirts | men_2013, men_2014, men_2015 | 10.90 EUR, 15 USD, 9 GBP | size_L | black | american_apparel | cotton   | usa                    |
    And the following product values:
      | product      | attribute           | value                           | locale | scope     |
      | tshirt-white | name                | T-shirt blanc                   | fr_FR  |           |
      | tshirt-white | name                | Weißes T-Shirt                  | de_DE  |           |
      | tshirt-white | description         | Un T-shirt blanc élégant        | fr_FR  | ecommerce |
      | tshirt-white | description         | Ein elegantes weißes T-Shirt    | de_DE  | ecommerce |
      | tshirt-white | customs_tax         | 4.20 EUR, 6 USD, 3.80 GBP       | de_DE  |           |
      | tshirt-white | washing_temperature | 40 CELSIUS                      |        |           |
      | tshirt-black | name                | T-shirt noir                    | fr_FR  |           |
      | tshirt-black | name                | Schwarzes T-Shirt               | de_DE  |           |
      | tshirt-black | description         | Un T-shirt noir élégant         | fr_FR  | ecommerce |
      | tshirt-black | description         | Ein elegantes schwarzes T-Shirt | de_DE  | ecommerce |
      | tshirt-black | washing_temperature | 40 CELSIUS                      |        |           |
    And I launched the completeness calculator
    And I am logged in as "Julia"
    When I am on the "ecommerce_product_export" export job page
    And I launch the export job
    And I wait for the "ecommerce_product_export" job to finish
    Then exported file of "ecommerce_product_export" should contain:
    """
    sku;additional_colors;categories;color;cost-EUR;cost-GBP;cost-USD;country_of_manufacture;customer_rating-ecommerce;customs_tax-de_DE-EUR;customs_tax-de_DE-GBP;customs_tax-de_DE-USD;datasheet;description-de_DE-ecommerce;description-en_GB-ecommerce;description-en_US-ecommerce;description-fr_FR-ecommerce;enabled;family;groups;handmade;image;legend-de_DE;legend-en_GB;legend-en_US;legend-fr_FR;manufacturer;material;name-de_DE;name-en_GB;name-en_US;name-fr_FR;number_in_stock-ecommerce;price-EUR;price-GBP;price-USD;release_date-ecommerce;size;thumbnail;washing_temperature;washing_temperature-unit;weight;weight-unit
    tshirt-white;;men_2013,men_2014,men_2015;white;;;;usa;;4.20;3.80;6.00;;"Ein elegantes weißes T-Shirt";;;"Un T-shirt blanc élégant";1;tshirts;;0;;;;;;american_apparel;cotton;"Weißes T-Shirt";;;"T-shirt blanc";;10.90;9.00;15.00;;size_M;;40;CELSIUS;;
    tshirt-black;;men_2013,men_2014,men_2015;black;;;;usa;;;;;;"Ein elegantes schwarzes T-Shirt";;;"Un T-shirt noir élégant";1;tshirts;;0;;;;;;american_apparel;cotton;"Schwarzes T-Shirt";;;"T-shirt noir";;10.90;9.00;15.00;;size_L;;40;CELSIUS;;
    """

  Scenario: Export attributes with full numeric codes
    Given a "footwear" catalog configuration
    And the following family:
      | code      | attributes                                                                                                       |
      | my_family | 123,color,description,lace_color,manufacturer,name,price,price,rating,side_view,size,top_view,weather_conditions |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And the following products:
      | sku      | family    | categories        | price          | size | color    | name-en_US | 123 |
      | SNKRS-1B | my_family | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    | aaa |
      | SNKRS-1R | my_family | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    | bbb |
      | SNKRS-1C | my_family | summer_collection | 55 EUR, 75 USD | 45   | charcoal | Model 1    | ccc |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contain:
    """
    sku;123;categories;color;description-en_US-mobile;enabled;family;groups;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    SNKRS-1B;aaa;summer_collection;black;;;1;my_family;;;;"Model 1";;50.00;70.00;;;45;;
    SNKRS-1R;bbb;summer_collection;red;;;1;my_family;;;;"Model 1";;50.00;70.00;;;45;;
    SNKRS-1C;ccc;summer_collection;charcoal;;;1;my_family;;;;"Model 1";;55.00;75.00;;;45;;
    """

  Scenario: Export attributes with a predefine order
    Given a "footwear" catalog configuration
    And the following family:
      | code      | attributes                                                                                                       |
      | my_family | 123,color,description,lace_color,manufacturer,name,price,price,rating,side_view,size,top_view,weather_conditions |
    And the following job "csv_footwear_product_export" configuration:
      | filePath | %tmp%/product_export/product_export.csv |
    And the following products:
      | sku      | family    | categories        | price          | size | color    | name-en_US | 123 |
      | SNKRS-1B | my_family | summer_collection | 50 EUR, 70 USD | 45   | black    | Model 1    | aaa |
      | SNKRS-1R | my_family | summer_collection | 50 EUR, 70 USD | 45   | red      | Model 1    | bbb |
      | SNKRS-1C | my_family | summer_collection | 55 EUR, 75 USD | 45   | charcoal | Model 1    | ccc |
    And I am logged in as "Julia"
    When I am on the "csv_footwear_product_export" export job page
    And I launch the export job
    And I wait for the "csv_footwear_product_export" job to finish
    Then exported file of "csv_footwear_product_export" should contains the following headers:
    """
    sku;categories;enabled;family;groups;123;color;description-en_US-mobile;lace_color;manufacturer;name-en_US;price-EUR;price-USD;rating;side_view;size;top_view;weather_conditions
    """

  Scenario: Successfully export products with a selection of attributes
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And the following job "tablet_product_export" configuration:
      | filters | {"structure":{"locales":["en_US"],"scope":"tablet","attributes":["price","size","color","cost","description","name","image","release_date","weight"]}, "data": []} |
    And the following products:
      | sku           | family  | categories                   | price                 | size   | color  | manufacturer     | material | country_of_manufacture |
      | tshirt-yellow | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | yellow | american_apparel | cotton   | usa                    |
      | tshirt-green  | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | green  | american_apparel | cotton   | usa                    |
    And the following product values:
      | product       | attribute       | value                                | locale | scope     |
      | tshirt-yellow | name            | Yellow t-shirt                       | en_US  |           |
      | tshirt-yellow | name            | Yellow t-shirt                       | en_GB  |           |
      | tshirt-yellow | name            | T-shirt blanc                        | fr_FR  |           |
      | tshirt-yellow | name            | Weißes T-Shirt                       | de_DE  |           |
      | tshirt-yellow | image           | %fixtures%/SNKRS-1R.png              |        |           |
      | tshirt-yellow | cost            | 10 EUR, 20 USD, 30 GBP               |        |           |
      | tshirt-yellow | release_date    | 2016-10-12                           |        | tablet    |
      | tshirt-yellow | customer_rating | 2                                    |        | tablet    |
      | tshirt-yellow | handmade        | 1                                    |        |           |
      | tshirt-yellow | weight          | 5 KILOGRAM                           |        |           |
      | tshirt-yellow | number_in_stock | 10                                   |        | tablet    |
      | tshirt-yellow | description     | A stylish yellow t-shirt             | en_US  | tablet    |
      | tshirt-yellow | description     | Un T-shirt blanc élégant             | fr_FR  | ecommerce |
      | tshirt-yellow | description     | A really stylish yellow t-shirt      | en_US  | print     |
      | tshirt-green  | name            | Green t-shirt                        | en_US  |           |
      | tshirt-green  | name            | Green t-shirt                        | en_GB  |           |
      | tshirt-green  | name            | T-shirt noir                         | fr_FR  |           |
      | tshirt-green  | name            | Schwarzes T-Shirt                    | de_DE  |           |
      | tshirt-green  | description     | Un T-shirt noir élégant              | fr_FR  | ecommerce |
      | tshirt-green  | description     | Ein elegantes schwarzes T-Shirt      | de_DE  | ecommerce |
      | tshirt-green  | description     | A really stylish green t-shirt       | en_US  | print     |
      | tshirt-green  | description     | Ein sehr elegantes schwarzes T-Shirt | de_DE  | print     |
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    sku;categories;color;cost-EUR;cost-GBP;cost-USD;description-en_US-tablet;enabled;family;groups;image;name-en_US;price-EUR;price-GBP;price-USD;release_date-tablet;size;weight;weight-unit
    tshirt-yellow;men_2013,men_2014,men_2015;yellow;10.00;20.00;30.00;;A stylish yellow t-shirt;1;tshirts;;files/tshirt-yellow/image/SNKRS-1R.png;Yellow t-shirt;10.00;9.00;15.00;2016-10-12;size_M;5;KILOGRAM
    tshirt-green;men_2013,men_2014,men_2015;green;;;;;;1;tshirts;;;Green t-shirt;10.00;9.00;15.00;;size_L;;
    """

  Scenario: Successfully export products with an empty array of attributes
    Given an "apparel" catalog configuration
    And I am logged in as "Julia"
    And the following job "tablet_product_export" configuration:
      | filters | {"structure":{"locales":["en_US"],"scope":"tablet","attributes":["sku"]}, "data": []} |
    And the following products:
      | sku           | family  | categories                   | price                 | size   | color  | manufacturer     | material | country_of_manufacture |
      | tshirt-yellow | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_M | yellow | american_apparel | cotton   | usa                    |
      | tshirt-green  | tshirts | men_2013, men_2014, men_2015 | 10 EUR, 15 USD, 9 GBP | size_L | green  | american_apparel | cotton   | usa                    |
    When I am on the "tablet_product_export" export job page
    And I launch the export job
    And I wait for the "tablet_product_export" job to finish
    Then exported file of "tablet_product_export" should contain:
    """
    sku;categories;enabled;family;groups
    tshirt-yellow;men_2013,men_2014,men_2015;1;tshirts;
    tshirt-green;men_2013,men_2014,men_2015;1;tshirts;
    """
