@acceptance-back
Feature: map the Pim identifiers with PIM.ai identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my Pim identifiers to the PIM.ai identifiers

  Scenario: successfully map PIM.ai attributes to pim attributes for the first time
    Given the following attribute:
      | code  | type                      |
      | brand | pim_catalog_text          |
      | mpn   | pim_catalog_simpleselect |
      | ean   | pim_catalog_identifier    |
      | asin  | pim_catalog_text          |
    When the identifiers are mapped with valid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |
    Then the identifiers mapping should be defined as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | ean            |
      | asin        | asin           |

  Scenario: fail to map PIM.ai attributes to invalid pim attributes
    Given the following attribute:
      | code  | type             |
      | brand | pim_catalog_text |
      | mpn   | pim_catalog_text |
      | ean   | pim_catalog_text |
      | asin  | pim_catalog_text |
    When the identifiers are mapped with invalid values as follows:
      | pim_ai_code | attribute_code |
      | brand       | burger         |
    Then the identifiers mapping should not be defined

  Scenario: successfully update an already existing mapping
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
    Then the identifiers mapping should be defined as follows:
      | pim_ai_code | attribute_code |
      | brand       | brand          |
      | mpn         | mpn            |
      | upc         | sku            |
      | asin        | id             |

  Scenario: get identifiers mapping
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
