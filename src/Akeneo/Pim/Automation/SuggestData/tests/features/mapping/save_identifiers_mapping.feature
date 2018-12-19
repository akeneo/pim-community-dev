@acceptance-back
Feature: Map the PIM identifiers with Franklin identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my PIM identifiers to the Franklin identifiers

  Background:
    Given Franklin is configured with a valid token

  @critical
  Scenario: Successfully map Franklin identifiers to PIM attributes for the first time
    Given the predefined attributes pim_brand, MPN, EAN and ASIN
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the retrieved identifiers mapping should be the following:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |

  Scenario: Successfully update an already existing identifiers mapping
    Given the predefined attributes pim_brand, MPN, EAN, ASIN, SKU and identifier
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | sku            |
      | asin          | identifier     |
    Then the retrieved identifiers mapping should be the following:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | sku            |
      | asin          | identifier     |

  Scenario Outline: Successfully map Franklin identifiers with valid PIM attribute types
    Given an empty identifiers mapping
    And the following attribute:
      | code      | type             |
      | pim_upc   | <attribute_type> |
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the retrieved identifiers mapping should be the following:
      | franklin_code | attribute_code |
      | brand         |                |
      | mpn           |                |
      | upc           | pim_upc        |
      | asin          |                |

    Examples:
      | attribute_type         |
      | pim_catalog_text       |
      | pim_catalog_identifier |

  Scenario Outline: Fails to map Franklin identifiers with invalid PIM attribute types
    Given an empty identifiers mapping
    And the following attribute:
      | code    | type             |
      | pim_upc | <attribute_type> |
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the identifiers mapping should not be saved

    Examples:
      | attribute_type                     |
      | pim_catalog_textarea               |
      | pim_catalog_price_collection       |
      | pim_assets_collection              |
      | pim_catalog_multiselect            |
      | pim_reference_data_multiselect     |
      | pim_reference_data_simpleselect    |
      | pim_catalog_image                  |
      | pim_catalog_file                   |
      | pim_catalog_boolean                |
      | pim_catalog_metric                 |
      | pim_catalog_date                   |
      | pim_catalog_number                 |
      | pim_catalog_simpleselect           |
      | akeneo_reference_entity_collection |
      | akeneo_reference_entity            |

  Scenario Outline: Fails to map Franklin identifiers with invalid PIM attributes properties
    Given an empty identifiers mapping
    And the following attribute:
      | code    | type             | localizable   | scopable   |
      | pim_upc | pim_catalog_text | <localizable> | <scopable> |
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the identifiers mapping should not be saved
    #And an invalid mapping message should be display

    Examples:
      | localizable | scopable |
      | true        | false    |
      | false       | true     |
      | true        | true     |

  Scenario: Fails to map Franklin identifiers with unexisting PIM attribute
    Given an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | burger         |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save an identifiers mapping with the brand without the MPN
    Given the predefined attributes pim_brand and EAN
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | upc           | ean            |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save an identifiers mapping with the MPN without the brand
    Given the predefined attributes MPN and EAN
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | mpn           | mpn            |
      | upc           | ean            |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save an empty identifiers mapping
    Given an empty identifiers mapping
    When the identifiers are mapped with empty values
    Then the identifiers mapping should not be saved

  Scenario: Fails to empty an identifiers mapping
    Given the predefined attributes ASIN
    And the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When the identifiers are mapped with empty values
    Then the retrieved identifiers mapping should be the following:
      | franklin_code | attribute_code |
      | asin          | asin           |

#  Scenario: Fails to map identifiers mapping when the token is invalid
#    Given the predefined attributes pim_brand, MPN, EAN and ASIN
#    And an empty identifiers mapping
#    And Franklin is configured with an expired token
#    When the identifiers are mapped as follows:
#      | franklin_code | attribute_code |
#      | brand         | pim_brand      |
#      | mpn           | mpn            |
#      | upc           | ean            |
#      | asin          | asin           |
#    Then the identifiers mapping should not be saved
#    And a token invalid message for mapping should be sent

  Scenario: Fails to map identifiers mapping when Franklin is down
    Given the predefined attributes pim_brand, MPN, EAN and ASIN
    And an empty identifiers mapping
    And Franklin server is down
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the identifiers mapping should not be saved
    And a data provider error message should be sent

  Scenario: Fails to update an already existing mapping when Franklin is down
    Given the predefined attributes pim_upc, EAN and ASIN
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | ean            |
      | asin          | asin           |
    And Franklin server is down
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the retrieved identifiers mapping should be the following:
      | franklin_code | attribute_code |
      | upc           | ean            |
      | asin          | asin           |
    And an invalid mapping message should be sent
