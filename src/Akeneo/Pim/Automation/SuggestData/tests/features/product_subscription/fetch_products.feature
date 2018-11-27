@acceptance-back
Feature: Fetch products from Franklin
  In order to automatically enrich my products
  As the System
  I want to fetch products I subscribed on from Franklin

  Scenario: Fail to fetch products if token is not configured
    Given Franklin is configured with an expired token
    And last fetch of subscribed products has been done yesterday
    When the subscribed products are fetched from Franklin
    Then 0 suggested data should have been added

  Scenario: Successfully fetch products from Franklin
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "606449099812" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
      | asin          | asin           |
    And the product "B00EYZY6AC" is subscribed to Franklin
    And the product "606449099812" is subscribed to Franklin
    And last fetch of subscribed products has been done yesterday
    #When the subscribed products are fetched from Franklin
    #Then 2 suggested data should have been added (APAI-153)

  Scenario: Successfully fetch no product from Franklin
    Given Franklin is configured with a valid token
    And last fetch of subscribed products has been done today
    When the subscribed products are fetched from Franklin
    Then 0 suggested data should have been added

  #Scenario: Identifiers mapping not configured or invalid

  #Scenario: Mapping attributes empty

  #Scenario: Successfully fetch products from Franklin from a specific date

  #Scenario: Successfully fetch products from Franklin from last launched time
