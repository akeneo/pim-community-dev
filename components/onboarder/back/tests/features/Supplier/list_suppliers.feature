Feature: Onboarder Serenity - Suppliers management - list suppliers

  Scenario: List suppliers
    Given there is a supplier with code "supplier2" and label "Supplier2"
    And there is a supplier with code "supplier1" and label "Supplier1"
    When I retrieve suppliers
    Then I should have the following suppliers:
      | code      | label      |
      | supplier1 | Supplier1 |
      | supplier2 | Supplier2 |

