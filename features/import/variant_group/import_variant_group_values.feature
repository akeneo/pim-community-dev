@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

  # what's tested here ?
  # -----------------------------|-------------|---------------|-------------
  # TYPE                         | VALID VALID | INVALID VALUE | NULL VALUE
  # -----------------------------|-------------|---------------|-------------
  # pim_catalog_boolean          | DONE        | TODO          | TODO
  # pim_catalog_date             | DONE        | TODO          | TODO
  # pim_catalog_file             | TODO        | TODO          | TODO
  # pim_catalog_identifier       | N/A         | N/A           | N/A
  # pim_catalog_image            | TODO        | TODO          | TODO
  # pim_catalog_metric           | DONE        | TODO          | TODO
  # pim_catalog_multiselect      | DONE        | TODO          | TODO
  # pim_catalog_number           | DONE        | TODO          | TODO
  # pim_catalog_price_collection | PARTIALLY   | TODO          | TODO
  # pim_catalog_simpleselect     | DONE        | TODO          | TODO
  # pim_catalog_text             | DONE        | TODO          | TODO
  # pim_catalog_textarea         | DONE        | TODO          | TODO

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku             | family  | categories        | size | color |
      | sandal-white-37 | sandals | winter_collection | 37   | white |
      | sandal-white-38 | sandals | winter_collection | 38   | white |
      | sandal-white-39 | sandals | winter_collection | 39   | white |
      | sandal-red-37   | sandals | winter_collection | 37   | red   |
      | sandal-red-38   | sandals | winter_collection | 38   | red   |
      | sandal-red-39   | sandals | winter_collection | 39   | red   |
    And the following product groups:
      | code   | label  | attributes  | type    | products                                                                                       |
      | SANDAL | Sandal | size, color | VARIANT | sandal-white-37, sandal-white-38, sandal-white-39, sandal-red-37, sandal-red-38, sandal-red-39 |
    And I am logged in as "Julia"

  Scenario: Successfully import a csv file of variant group values with localizable, scopable textarea
    Given the following CSV file to import:
      """
      variant_group_code;name-en_US;description-en_US-tablet
      SANDAL;My sandal;My sandal description for locale en_US and channel tablet
      """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the english tablet name of "sandal-white-37" should be "My sandal"
    And the english tablet description of "sandal-white-37" should be "My sandal description for locale en_US and channel tablet"
    And the english tablet name of "sandal-white-38" should be "My sandal"
    And the english tablet description of "sandal-white-38" should be "My sandal description for locale en_US and channel tablet"

  Scenario: Successfully import a csv file of variant group values with numbers
    Given the following CSV file to import:
    """
    variant_group_code;number_in_stock
    SANDAL;44
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | number_in_stock | 44 |
    And the product "sandal-white-38" should have the following value:
      | number_in_stock | 44 |

  Scenario: Successfully import a csv file of variant group values with simple select
    Given the following CSV file to import:
    """
    variant_group_code;manufacturer
    SANDAL;Converse
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | manufacturer | Converse |
    And the product "sandal-white-38" should have the following value:
      | manufacturer | Converse |

  Scenario: Successfully import a csv file of variant group values with multi select
    Given the following CSV file to import:
    """
    variant_group_code;weather_conditions
    SANDAL;dry,wet
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | weather_conditions | Dry, Wet |
    And the product "sandal-white-38" should have the following value:
      | weather_conditions | Dry, Wet |

  Scenario: Successfully import a csv file of variant group values with dates
    Given the following CSV file to import:
    """
    variant_group_code;destocking_date
    SANDAL;2015-12-14
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | destocking_date | 2015-12-14T00:00:00+0100 |
    And the product "sandal-white-38" should have the following value:
      | destocking_date | 2015-12-14T00:00:00+0100 |

  Scenario: Successfully import a csv file of variant group values with booleans (to true)
    Given the following CSV file to import:
    """
    variant_group_code;handmade
    SANDAL;1
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | handmade | 1 |
    And the product "sandal-white-38" should have the following value:
      | handmade | 1 |

  Scenario: Successfully import a csv file of variant group values with booleans (to false)
    Given the following CSV file to import:
    """
    variant_group_code;handmade
    SANDAL;0
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | handmade | |
    And the product "sandal-white-38" should have the following value:
      | handmade | |

  Scenario: Successfully import a csv file of variant group values with prices as many fields
    Given the following CSV file to import:
    """
    variant_group_code;price-EUR;price-USD
    SANDAL;100;90
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "sandal-white-38" should have the following value:
      | price | 100.00 EUR, 90.00 USD |

  @javascript @skip the following format for prices is not supported for now
  Scenario: Successfully import a csv file of variant group values with prices as one field
    Given the following CSV file to import:
    """
    variant_group_code;price
    SANDAL;100 EUR, 90 USD
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | price | 100.00 EUR, 90.00 USD |
    And the product "sandal-white-38" should have the following value:
      | price | 100.00 EUR, 90.00 USD |

  Scenario: Successfully import a csv file of variant group values with metrics in many fields
    Given the following CSV file to import:
    """
    variant_group_code;length;length-unit
    SANDAL;4000;CENTIMETER
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | length | 4000.0000 CENTIMETER |
    And the product "sandal-white-38" should have the following value:
      | length | 4000.0000 CENTIMETER |

  @javascript @skip the following format for metric is not supported for now
  Scenario: Successfully import a csv file of variant group values with metrics in a single field
    Given the following CSV file to import:
    """
    variant_group_code;length
    SANDAL;4000 CENTIMETER
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | length | 4000.0000 CENTIMETER |
    And the product "sandal-white-38" should have the following value:
      | length | 4000.0000 CENTIMETER |

  @javascript @skip not implemented
  Scenario: Successfully import a csv file of variant group values with medias and files
    Given the following attributes:
      | label       | type  | allowed extensions |
      | Front view  | image | gif, jpg           |
      | User manual | file  | txt, pdf           |
    And the following CSV file to import:
    """
    variant_group_code;frontView;name-en_US;userManual
    SANDAL;bic-core-148.gif;"Bic Core 148";bic-core-148.txt
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    And import directory of "footwear_variant_group_values_import" contains the following media:
      | bic-core-148.gif        |
      | bic-core-148.txt        |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |
    And the product "sandal-white-38" should have the following values:
      | frontView  | bic-core-148.gif |
      | userManual | bic-core-148.txt |