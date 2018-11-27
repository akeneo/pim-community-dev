@acceptance-back
Feature: Map the PIM identifiers with Franklin identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my PIM identifiers to the Franklin identifiers

  Background:
    Given Franklin is configured with a valid token

  @critical
  Scenario: Successfully map Franklin attributes to PIM attributes for the first time
    Given the predefined attributes pim_brand, MPN, EAN and ASIN
    And an empty identifiers mapping
    When the identifiers are mapped with valid values as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the retrieved mapping should be the following:
      | franklin_code | attribute_code | en_US | fr_FR  |
      | brand         | pim_brand      | Brand | Marque |
      | mpn           | mpn            | MPN   | MPN    |
      | upc           | ean            |       |        |
      | asin          | asin           | ASIN  | ASIN   |

  Scenario: Successfully update an already existing mapping
    Given the predefined attributes pim_brand, MPN, EAN, ASIN, SKU and identifier
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    When the identifiers are mapped with valid values as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | sku            |
      | asin          | identifier     |
    Then the retrieved mapping should be the following:
      | franklin_code | attribute_code | en_US | fr_FR  |
      | brand         | pim_brand      | Brand | Marque |
      | mpn           | mpn            | MPN   | MPN    |
      | upc           | sku            | SKU   | UGS    |
      | asin          | identifier     |       |        |

  Scenario Outline: Successfully map Franklin attributes with valid PIM attribute types
    Given an empty identifiers mapping
    And the following attribute:
      | code      | type             |
      | pim_upc   | <attribute_type> |
    When the identifiers are mapped with valid values as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    Then the retrieved mapping should be the following:
      | franklin_code | attribute_code |
      | brand         |                |
      | mpn           |                |
      | upc           | pim_upc        |
      | asin          |                |

    Examples:
      | attribute_type         |
      | pim_catalog_text       |
      | pim_catalog_identifier |

  Scenario Outline: Fails to map Franklin attributes with invalid PIM attribute types
    Given an empty identifiers mapping
    And the following attribute:
      | code    | type             |
      | pim_upc | <attribute_type> |
    When the identifiers are mapped with valid values as follows:
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

  Scenario: Fails to map Franklin attribute with unexisting PIM attribute
    Given an empty identifiers mapping
    When the identifiers are mapped with invalid values as follows:
      | franklin_code | attribute_code |
      | brand         | burger         |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save a mapping with the brand without the MPN
    Given the predefined attributes pim_brand and EAN
    And an empty identifiers mapping
    When the identifiers are mapped with invalid values as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | upc           | ean            |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save a mapping with the MPN without the brand
    Given the predefined attributes MPN and EAN
    And an empty identifiers mapping
    When the identifiers are mapped with invalid values as follows:
      | franklin_code | attribute_code |
      | mpn           | mpn            |
      | upc           | ean            |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save an empty identifiers mapping
    Given an empty identifiers mapping
    When the identifiers mapping is saved with empty values
    Then the identifiers mapping should not be saved

  Scenario: Fails to empty an identifiers mapping
    Given the predefined attributes ASIN
    And the identifiers are mapped with valid values as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When the identifiers mapping is saved with empty values
    Then the identifiers mapping should not be saved

  Scenario: Fails to map identifiers mapping when Franklin is down
    Given the predefined attributes pim_brand, MPN, EAN and ASIN
    And an empty identifiers mapping
    And Franklin is configured with an expired token
    When the identifiers are mapped with valid values as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the retrieved mapping should be the following:
      | franklin_code | attribute_code | en_US | fr_FR  |
      | brand         | pim_brand      | Brand | Marque |
      | mpn           | mpn            | MPN   | MPN    |
      | upc           | ean            |       |        |
      | asin          | asin           | ASIN  | ASIN   |

  Scenario: Fails to map identifiers mapping when Franklin is down
    Given the predefined attributes pim_brand, MPN, EAN and ASIN
    And an empty identifiers mapping
    And Franklin server is down
    When the identifiers are mapped with valid values as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    Then the retrieved mapping should be the following:
      | franklin_code | attribute_code | en_US | fr_FR  |
      | brand         | pim_brand      | Brand | Marque |
      | mpn           | mpn            | MPN   | MPN    |
      | upc           | ean            |       |        |
      | asin          | asin           | ASIN  | ASIN   |
