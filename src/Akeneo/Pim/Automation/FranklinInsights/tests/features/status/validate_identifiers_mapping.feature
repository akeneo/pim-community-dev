@acceptance-back
Feature: Define the identifiers mapping status
  In order to automatically enrich my products
  As a system administrator
  I want to map my PIM identifiers to the Franklin identifiers

  Background:
    Given Franklin is configured with a valid token

  Scenario: An identifiers mapping is valid if at least ASIN is mapped
    Given the predefined attributes asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When I retrieve the connection status
    Then the identifiers mapping should be valid

  Scenario: An identifiers mapping is valid if at least UPC is mapped
    Given the predefined attributes ean
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | ean            |
    When I retrieve the connection status
    Then the identifiers mapping should be valid

  Scenario: An identifiers mapping is valid if at least MPN and BRAND are mapped
    Given the predefined attributes pim_brand and mpn
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
    When I retrieve the connection status
    Then the identifiers mapping should be valid

  Scenario: An identifiers mapping is not valid if MPN is mapped without BRAND
    Given the predefined attributes pim_brand and mpn
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
    When I delete the attribute mapped to brand
    And I retrieve the connection status
    Then the identifiers mapping should not be valid

  Scenario: An identifiers mapping is not valid if BRAND is mapped without MPN
    Given the predefined attributes pim_brand and mpn
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
    When I delete the attribute mapped to mpn
    And I retrieve the connection status
    Then the identifiers mapping should not be valid

  Scenario: An identifiers mapping is not valid if nothing is mapped
    Given an empty identifiers mapping
    When I retrieve the connection status
    Then the identifiers mapping should not be valid
