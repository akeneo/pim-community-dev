@acceptance-back
  #TODO: TO REWORK and put inside save_identifiers_mapping
Feature: Map the PIM identifiers with Franklin identifiers
  In order to automatically enrich my products
  As a system administrator
  I want to map my PIM identifiers to the Franklin identifiers

  Background:
    Given Franklin is configured with a valid token

  Scenario: An identifiers mapping is valid if at least ASIN is mapped
    Given the predefined attributes ASIN
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    Then identifiers mapping should be valid

  Scenario: An identifiers mapping is valid if at least UPC is mapped
    Given the predefined attributes EAN
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | upc           | ean            |
    Then identifiers mapping should be valid

  Scenario: An identifiers mapping is valid if at least MPN and BRAND are mapped
    Given the predefined attributes pim_brand and MPN
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
    Then identifiers mapping should be valid

  Scenario: An identifiers mapping is not valid if MPN is mapped without BRAND
    Given the predefined attributes pim_brand
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
    Then identifiers mapping should not be valid

  Scenario: An identifiers mapping is not valid if BRAND is mapped without MPN
    Given the predefined attributes mpn
    When the identifiers are mapped as follows:
      | franklin_code | attribute_code |
      | mpn           | mpn            |
    Then identifiers mapping should not be valid

  Scenario: An identifiers mapping is not valid if nothing is mapped
    Given an empty identifiers mapping
    Then identifiers mapping should not be valid
