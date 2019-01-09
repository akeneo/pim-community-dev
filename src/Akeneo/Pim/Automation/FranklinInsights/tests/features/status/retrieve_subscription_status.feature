@acceptance-back
Feature: Fetch subscription status
  In order to know if product is already subscribed and if I can subscribe
  As the System
  I want to fetch the subscription status

  Scenario: I retrieve the subscription status for a product without family
    Given the product without family "product_without_family"
    When I retrieve the subscription status of the product "product_without_family"
    Then the subscription status should not have any family

  Scenario: I retrieve the subscription status for a product with family
    Given the product "606449099812" of the family "router"
    When I retrieve the subscription status of the product "606449099812"
    Then the subscription status should have a family

  Scenario: I retrieve the subscription status for a product already subscribed
    Given the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I retrieve the subscription status of the product "B00EYZY6AC"
    Then the subscription status should be subscribed

  Scenario: I retrieve the subscription status for a product that have not been subscribed
    Given the product "B00EYZY6AC" of the family "router"
    When I retrieve the subscription status of the product "B00EYZY6AC"
    Then the subscription status should not be subscribed

  Scenario: I retrieve the subscription status for a product which has no values for the identifiers mapping
    Given the product "606449099812" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | asin          | asin           |
    When I retrieve the subscription status of the product "606449099812"
    Then the subscription status should indicate that the mapping values are not filled

  Scenario: I retrieve the subscription status for a product which has values for the identifiers mapping
    Given the product "606449099812" of the family "router"
    And a predefined identifiers mapping as follows:
      | franklin_code | attribute_code |
      | upc           | pim_upc        |
    When I retrieve the subscription status of the product "606449099812"
    Then the subscription status should indicate that the mapping values are filled

  Scenario: I retrieve the subscription status for a non variant product
    Given the product "B00EYZY6AC" of the family "router"
    When I retrieve the subscription status of the product "B00EYZY6AC"
    Then the subscription status should indicate that the product is not a variant

#  Scenario: I retrieve the subscription status for a variant product
#    Given the variant product "variant_product"
#    When I retrieve the subscription status of the product "variant_product"
#    Then the subscription status should indicate that the product is a variant

  Scenario: I retrieve the connection status from the subscription status
    Given the product "B00EYZY6AC" of the family "router"
    And Franklin is configured with a valid token
    When I retrieve the subscription status of the product "B00EYZY6AC"
    Then the subscription status should indicate that Franklin is activated
