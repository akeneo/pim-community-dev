@javascript
Feature: Execute an import with valid data
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  # what's tested here ?
  # -----------------------------|-------------|
  # TYPE                         | VALID DATA  |
  # -----------------------------|-------------|
  # pim_catalog_boolean          | DONE        |
  # pim_catalog_date             | DONE        |
  # pim_catalog_file             | DONE        |
  # pim_catalog_identifier       | N/A         |
  # pim_catalog_image            | DONE        |
  # pim_catalog_metric           | DONE        |
  # pim_catalog_multiselect      | DONE        |
  # pim_catalog_number           | DONE        |
  # pim_catalog_price_collection | DONE        |
  # pim_catalog_simpleselect     | DONE        |
  # pim_catalog_text             | DONE        |
  # pim_catalog_textarea         | DONE        |

  Background:
    Given the "footwear" catalog configuration
    And the following variant groups:
      | code   | label-en_US | axis       | type    |
      | SANDAL | Sandal      | size,color | VARIANT |
    And the following products:
      | sku             | family  | categories        | size | color | groups |
      | sandal-white-37 | sandals | winter_collection | 37   | white | SANDAL |
      | sandal-white-38 | sandals | winter_collection | 38   | white | SANDAL |
      | sandal-white-39 | sandals | winter_collection | 39   | white | SANDAL |
      | sandal-red-37   | sandals | winter_collection | 37   | red   | SANDAL |
      | sandal-red-38   | sandals | winter_collection | 38   | red   | SANDAL |
      | sandal-red-39   | sandals | winter_collection | 39   | red   | SANDAL |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of variant group values with localizable, scopable textarea
    Given the following CSV file to import:
      """
      code;type;name-en_US;description-en_US-tablet
      SANDAL;VARIANT;My sandal;My sandal description for locale en_US and channel tablet
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the english localizable value name of "sandal-white-37" should be "My sandal"
    And the english tablet description of "sandal-white-37" should be "My sandal description for locale en_US and channel tablet"
    And the english localizable value name of "sandal-white-38" should be "My sandal"
    And the english tablet description of "sandal-white-38" should be "My sandal description for locale en_US and channel tablet"

  Scenario: Successfully import a csv file of variant group values with numbers
    Given the following CSV file to import:
      """
      code;type;number_in_stock
      SANDAL;VARIANT;44
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | number_in_stock | 44 |
    And the product "sandal-white-38" should have the following value:
      | number_in_stock | 44 |

  Scenario: Successfully import a csv file of variant group values with simple select
    Given the following CSV file to import:
      """
      code;type;manufacturer
      SANDAL;VARIANT;Converse
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | manufacturer | [Converse] |
    And the product "sandal-white-38" should have the following value:
      | manufacturer | [Converse] |

  Scenario: Successfully import a csv file of variant group values with multi select
    Given the following CSV file to import:
      """
      code;type;weather_conditions
      SANDAL;VARIANT;dry,wet
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | weather_conditions | [dry], [wet] |
    And the product "sandal-white-38" should have the following value:
      | weather_conditions | [dry], [wet] |

  Scenario: Successfully import a csv file of variant group values with dates
    Given the following CSV file to import:
      """
      code;type;destocking_date
      SANDAL;VARIANT;2015-12-14
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | destocking_date | 2015-12-14 |
    And the product "sandal-white-38" should have the following value:
      | destocking_date | 2015-12-14 |

  Scenario: Successfully import a csv file of variant group values with booleans (to true)
    Given the following CSV file to import:
      """
      code;type;handmade
      SANDAL;VARIANT;1
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | handmade | 1 |
    And the product "sandal-white-38" should have the following value:
      | handmade | 1 |

  Scenario: Successfully import a csv file of variant group values with booleans (to false)
    Given the following CSV file to import:
      """
      code;type;handmade
      SANDAL;VARIANT;0
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | handmade |  |
    And the product "sandal-white-38" should have the following value:
      | handmade |  |

  Scenario: Successfully import a csv file of variant group values with prices as many fields
    Given the following CSV file to import:
      """
      code;type;price-EUR;price-USD
      SANDAL;VARIANT;100;90
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "sandal-white-38" should have the following value:
      | price | 100.00 EUR, 90.00 USD |

  Scenario: Successfully import a csv file of variant group values with prices as one field
    Given the following CSV file to import:
      """
      code;type;price
      SANDAL;VARIANT;100 EUR, 90 USD
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "sandal-white-38" should have the following value:
      | price | 100.00 EUR, 90.00 USD |

  Scenario: Successfully import a csv file of variant group values with metrics in many fields
    Given the following CSV file to import:
      """
      code;type;length;length-unit
      SANDAL;VARIANT;4000;CENTIMETER
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | length | 4000.0000 CENTIMETER |
    And the product "sandal-white-38" should have the following value:
      | length | 4000.0000 CENTIMETER |

  Scenario: Successfully import a csv file of variant group values with metrics in a single field
    Given the following CSV file to import:
      """
      code;type;length
      SANDAL;VARIANT;4000 CENTIMETER
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following value:
      | length | 4000.0000 CENTIMETER |
    And the product "sandal-white-38" should have the following value:
      | length | 4000.0000 CENTIMETER |

  Scenario: Successfully import a csv file of variant group values with medias and files
    Given the following attributes:
      | label-en_US | type              | allowed_extensions | group | code       |
      | Front view  | pim_catalog_image | gif, jpg           | other | frontView  |
      | User manual | pim_catalog_file  | txt, pdf           | other | userManual |
    And the following CSV file to import:
      """
      code;type;frontView;name-en_US;userManual
      SANDAL;VARIANT;bic-core-148.gif;"Bic Core 148";bic-core-148.txt
      """
    And the following job "csv_footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    And import directory of "csv_footwear_variant_group_import" contains the following media:
      | bic-core-148.gif |
      | bic-core-148.txt |
    When I am on the "csv_footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_variant_group_import" job to finish
    Then there should be 6 products
    And I should see "read lines 1"
    And I should see "Processed 1"
    And I should see "Updated products 6"
    And the product "sandal-white-37" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
    And the product "sandal-white-38" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
