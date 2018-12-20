@acceptance-back
Feature: Map the PIM identifiers with Franklin identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my PIM identifiers to the Franklin identifiers

  Background:
    Given Franklin is configured with a valid token

  @critical
  Scenario: Successfully map Franklin identifiers to PIM attributes for the first time
    Given the predefined attributes pim_brand, mpn, ean and asin
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the identifiers mapping should be saved as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |

  Scenario: Successfully update an already existing identifiers mapping
    Given the predefined attributes pim_brand, mpn, ean, asin, sku and identifier
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
    Then the identifiers mapping should be saved as follows:
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
    Then the identifiers mapping should be saved as follows:
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
    And an invalid identifier pim_upc attribute type message should be sent

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

  Scenario: Fails to map Franklin identifiers with localizable PIM attributes
    Given an empty identifiers mapping
    And the following attribute:
      | code    | type             | localizable |
      | pim_upc | pim_catalog_text | true        |
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the identifiers mapping should not be saved
    And an invalid identifier pim_upc localizable message should be sent

  Scenario: Fails to map Franklin identifiers with scopable PIM attributes
    Given an empty identifiers mapping
    And the following attribute:
      | code    | type             | scopable |
      | pim_upc | pim_catalog_text | true        |
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the identifiers mapping should not be saved
    And an invalid identifier pim_upc scopable message should be sent

  Scenario: Fails to map Franklin identifiers with locale specific PIM attributes
    Given an empty identifiers mapping
    And the following locales "en_US"
    And the following text attribute "pim_upc" specific to locale en_US
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the identifiers mapping should not be saved
    And an invalid identifier pim_upc locale specific message should be sent

  Scenario: Fails to map Franklin identifiers with unexisting PIM attribute
    Given an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | burger         |
    Then the identifiers mapping should not be saved
    And a not existing identifier attribute message should be sent

  Scenario: Fails to save an identifiers mapping with the brand without the MPN
    Given the predefined attributes pim_brand and ean
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | upc           | ean            |
    Then the identifiers mapping should not be saved
    And an invalid brand mpn identifier message should be sent

  Scenario: Fails to save an identifiers mapping with the MPN without the brand
    Given the predefined attributes MPN and ean
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | mpn           | mpn            |
      | upc           | ean            |
    Then the identifiers mapping should not be saved
    And an invalid brand mpn identifier message should be sent

  Scenario: Fails to save an empty identifiers mapping
    Given an empty identifiers mapping
    When the identifiers are mapped with empty values
    Then the identifiers mapping should not be saved
    And a missing or invalid identifiers message should be sent

  Scenario: Fails to empty an identifiers mapping
    Given the predefined attributes asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When the identifiers are mapped with empty values
    Then the identifiers mapping should not be saved
    And a missing or invalid identifiers message should be sent

  Scenario: Fails to map twice the same attribute on different identifiers
    Given the predefined attributes asin
    And an empty identifiers mapping
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | asin           |
      | asin          | asin           |
    Then the identifiers mapping should not be saved
    And a duplicate identifiers attribute message should be sent

  Scenario: Fails to map identifiers mapping when the token is invalid
    Given the predefined attributes pim_brand, mpn, ean and asin
    And an empty identifiers mapping
    And Franklin is configured with an expired token
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the identifiers mapping should not be saved
    And an authentication error message should be sent

  Scenario: Fails to map identifiers mapping when Franklin is down
    Given the predefined attributes pim_brand, mpn, ean and asin
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
    Given the predefined attributes pim_upc, ean and asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | ean            |
      | asin          | asin           |
    And Franklin server is down
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the identifiers mapping should not be saved
    And a data provider error message should be sent
