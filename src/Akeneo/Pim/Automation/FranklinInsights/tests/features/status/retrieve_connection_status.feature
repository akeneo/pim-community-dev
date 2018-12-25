@acceptance-back
Feature: Fetch product subscription status
  In order to know if product is already subscribed and if I can subscribe
  As the System
  I want to fetch product subscription statuses

  # TO REWORK on Connection status
  Scenario: I retrieved the product subscription status for a product which has no values for the identifiers mapping
    Given the product "606449099812" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When I retrieve the product subscription status of the product "606449099812"
    Then the product subscription status indicates that mapping is not filled

  # TO REWORK on Connection status
  Scenario: I retrieved the product subscription status for a product which has values for the identifiers mapping
    Given the product "606449099812" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I retrieve the product subscription status of the product "606449099812"
    Then the product subscription status indicates that mapping is filled
