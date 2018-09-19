@acceptance-back
Feature: Map the PIM identifiers with PIM.ai identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my PIM identifiers to the PIM.ai identifiers

  Background:
    Given PIM.ai is configured with a valid token

  Scenario: Successfully retrieve the mapping for the display
    Given the following attribute:
      | code  | type             |
      | brand | pim_catalog_text |
      | mpn   | pim_catalog_text |
      | ean   | pim_catalog_text |
      | asin  | pim_catalog_text |
    When the identifiers are mapped with valid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |
    Then the retrieved mapping should be the following:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |

  Scenario: Successfully map PIM.ai attributes to PIM attributes for the first time
    Given the following attribute:
      | code  | type                      |
      | brand | pim_catalog_text          |
      | mpn   | pim_catalog_simpleselect  |
      | ean   | pim_catalog_identifier    |
      | asin  | pim_catalog_text          |
    And an empty identifiers mapping
    When the identifiers are mapped with valid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |
    Then the retrieved mapping should be the following:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |

  Scenario: Successfully update an already existing mapping
    Given the following attribute:
      | code  | type                   |
      | brand | pim_catalog_text       |
      | mpn   | pim_catalog_text       |
      | ean   | pim_catalog_text       |
      | asin  | pim_catalog_text       |
      | sku   | pim_catalog_identifier |
      | id    | pim_catalog_identifier |
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |
    When the identifiers are mapped with valid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | sku            |
      | asin        | id           |
    Then the retrieved mapping should be the following:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | sku            |
      | asin        | id             |

  Scenario Outline: Successfully map PIM.ai attributes with valid PIM attribute types
    Given an empty identifiers mapping
    And the following attribute:
      | code  | type             |
      | brand | <attribute_type> |
    When the identifiers are mapped with valid values as follows:
      | pim_ai_code | attribute_code |
      | upc       | brand          |
    Then the identifiers mapping should be valid

    Examples:
      | attribute_type           |
      | pim_catalog_text         |
      | pim_catalog_simpleselect |
      | pim_catalog_identifier   |
      | pim_catalog_number       |

  Scenario Outline: Fails to map PIM.ai attributes with invalid PIM attribute types
    Given an empty identifiers mapping
    And the following attribute:
      | code  | type             |
      | brand | <attribute_type> |
    When the identifiers are mapped with valid values as follows:
      | pim_ai_code | attribute_code |
      | upc       | brand          |
    Then the identifiers mapping should not be saved

    Examples:
      | attribute_type                    |
      | pim_catalog_textarea              |
      | pim_catalog_price_collection      |
      | pim_assets_collection             |
      | pim_catalog_multiselect           |
      | pim_reference_data_multiselect    |
      | pim_reference_data_simpleselect   |
      | pim_catalog_image                 |
      | pim_catalog_file                  |
      | pim_catalog_boolean               |
      | pim_catalog_metric                |
      | pim_catalog_date                  |
      | akeneo_enriched_entity_collection |

  Scenario: Fails to map PIM.ai attribute with unexisting PIM attribute
    Given an empty identifiers mapping
    When the identifiers are mapped with invalid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | burger         |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save a mapping with the brand without the MPN
    Given the following attribute:
      | code  | type                      |
      | brand | pim_catalog_text          |
      | mpn   | pim_catalog_simpleselect  |
      | ean   | pim_catalog_identifier    |
    And an empty identifiers mapping
    When the identifiers are mapped with invalid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | upc         | ean            |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save a mapping with the MPN without the brand
    Given the following attribute:
      | code  | type                      |
      | brand | pim_catalog_text          |
      | mpn   | pim_catalog_simpleselect  |
      | ean   | pim_catalog_identifier    |
    And an empty identifiers mapping
    When the identifiers are mapped with invalid values as follows:
      | pim_ai_code | attribute_code |
      | mpn         | mpn          |
      | upc         | ean            |
    Then the identifiers mapping should not be saved

  Scenario: Fails to save an empty identifiers mapping
    Given an empty identifiers mapping
    When the identifiers mapping is saved with empty values
    Then the identifiers mapping should not be saved
