Feature: Onboarder Serenity - Suppliers management - list suppliers

  Scenario: List suppliers
    Given a supplier with code "supplier2" and label "Supplier2"
    And a supplier with code "supplier1" and label "Supplier1"
    When I retrieve the suppliers
    Then I should have the following suppliers:
      | code      | label     |
      | supplier1 | Supplier1 |
      | supplier2 | Supplier2 |

