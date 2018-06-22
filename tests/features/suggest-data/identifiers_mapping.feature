Feature: map the Pim identifiers with Pim.ai identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my Pim identifiers to the Pim.ai identifiers

  @acceptance-back
  Scenario: successfully map pim.ai attributes to pim attributes
    Given the following attribute:
      | code  | type             |
      | brand | pim_catalog_text |
      | mpn   | pim_catalog_text |
      | ean   | pim_catalog_text |
      | asin  | pim_catalog_text |
    When I map pim.ai identifiers to my pim identifiers with valid attributes
    Then the identifiers mapping is set

  @acceptance-back
  Scenario: fails to map pim.ai attributes to pim attributes
    Given the following attribute:
      | code  | type             |
      | brand | pim_catalog_text |
      | mpn   | pim_catalog_text |
      | ean   | pim_catalog_text |
      | asin  | pim_catalog_text |
    When I map pim.ai identifiers to my pim identifiers with invalid attributes
    Then the identifiers mapping is not set
