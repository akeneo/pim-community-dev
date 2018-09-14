@acceptance-back
Feature: Fetch products from PIM.ai
  In order to automatically enrich my products
  As the System
  I want to fetch products I subscribed on from PIM.ai

  Scenario: Fail to fetch products if token is not configured
    Given the PIM.ai token is expired
    And last fetch of subscribed products has been done yesterday
    When the subscribed products are fetched from PIM.ai
    Then 0 suggested data should have been added

  Scenario: Successfully fetch products from PIM.ai
    Given PIM.ai is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "606449099812" of the family "router"
    And a predefined mapping as follows:
      | pim_ai_code | attribute_code |
      | upc         | pim_upc        |
      | asin        | asin           |
    And the product "B00EYZY6AC" is subscribed to PIM.ai
    And the product "606449099812" is subscribed to PIM.ai
    And last fetch of subscribed products has been done yesterday
    #When the subscribed products are fetched from PIM.ai
    #Then 2 suggested data should have been added (APAI-153)

  Scenario: Successfully fetch no product from PIM.ai
    Given PIM.ai is configured with a valid token
    And last fetch of subscribed products has been done today
    When the subscribed products are fetched from PIM.ai
    Then 0 suggested data should have been added

  #Scenario: Identifiers mapping not configured or invalid

  #Scenario: Mapping attributes empty

  #Scenario: Successfully fetch products from PIM.ai from a specific date

  #Scenario: Successfully fetch products from PIM.ai from last launched time
