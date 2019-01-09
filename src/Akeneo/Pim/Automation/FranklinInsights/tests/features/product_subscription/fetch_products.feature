@acceptance-back
Feature: Fetch products from Franklin
  In order to automatically enrich my products
  As the System
  I want to fetch products I subscribed on from Franklin

  @critical
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
    When the subscribed products are fetched from Franklin
#    Then there should be a proposal for product B00EYZY6AC
#    And there should be a proposal for product 606449099812

  Scenario: Successfully fetch no product from Franklin
    Given Franklin is configured with a valid token
    And last fetch of subscribed products has been done today
    When the subscribed products are fetched from Franklin
    Then there should not have any proposal

  Scenario: Fail to fetch products if token is not configured
    Given Franklin is configured with an expired token
    And last fetch of subscribed products has been done yesterday
    When the subscribed products are fetched from Franklin
    Then there should not have any proposal
    And an authentication error message should be sent

  Scenario: Fail to fetch products from Franklin when server is down
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    And the product "B00EYZY6AC" is subscribed to Franklin
    And last fetch of subscribed products has been done yesterday
    And Franklin server is down
    When the subscribed products are fetched from Franklin
    Then there should not have any proposal
    And a data provider error message should be sent

  #Scenario: Successfully fetch products from Franklin from a specific date

  #Scenario: Successfully fetch products from Franklin from last launched time
