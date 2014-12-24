@javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be able to import variant group values in product values

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

  @skip not implemented, waiting for json denormalization #TODO must return a boolean to be use in updater
  Scenario: Successfully import a csv file of variant group values with booleans
    Given the following CSV file to import:
    """
    variant_group_code;handmade
    SANDAL;true
    """
    And the following job "footwear_variant_group_values_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_values_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_values_import" job to finish
    Then there should be 6 products
    And the product "sandal-white-37" should have the following value:
      | handmade | true |
    And the product "sandal-white-38" should have the following value:
      | handmade | true |

  @skip not implemented, waiting for json denormalization #TODO
  Scenario: Successfully import a csv file of variant group values with prices
    Given the following CSV file to import:
    """
    variant_group_code;price
    SANDAL;"100 EUR, 90 USD"
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

  @skip not implemented, waiting for json denormalization #TODO
  Scenario: Successfully import a csv file of variant group values with metrics
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

  @skip not implemented, waiting for json denormalization #TODO
  Scenario: Successfully import a csv file of variant group values with media