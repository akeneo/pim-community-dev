@acceptance-back
Feature: Retrieve identifiers mapping from Franklin

  Background:
    Given Franklin is configured with a valid token

  Scenario: Successfully retrieve an empty identifiers mapping
    Given an empty identifiers mapping
    When I retrieve the identifiers mapping
    Then the retrieved identifiers mapping should be empty

  Scenario: Successfully retrieve the identifiers mapping
    Given the predefined attributes pim_brand, mpn, ean and asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |
    When I retrieve the identifiers mapping
    Then the retrieved identifiers mapping should be:
      | franklin_code | attribute_code |
      | brand         | pim_brand      |
      | mpn           | mpn            |
      | upc           | ean            |
      | asin          | asin           |

  Scenario: Successfully retrieve the identifiers mapping when Franklin is down
    Given the predefined attributes ean and asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | ean            |
      | asin          | asin           |
    And Franklin server is down
    When I retrieve the identifiers mapping
    Then the retrieved identifiers mapping should be:
      | franklin_code | attribute_code |
      | brand         |                |
      | mpn           |                |
      | upc           | ean            |
      | asin          | asin           |

  Scenario: Successfully retrieve the identifiers mapping when the token is invalid
    Given the predefined attributes ean and asin
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | ean            |
      | asin          | asin           |
    And Franklin is configured with an expired token
    When I retrieve the identifiers mapping
    Then the retrieved identifiers mapping should be:
      | franklin_code | attribute_code |
      | upc           | ean            |
      | asin          | asin           |
