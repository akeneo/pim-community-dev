Feature: Onboarder Serenity - Suppliers management - list suppliers

  Scenario: List suppliers
    Given a supplier with code "supplier2" and label "Supplier2" and "2" contributors
    And a supplier with code "supplier1" and label "Supplier1" and "0" contributors
    When I retrieve the suppliers
    Then I should have the following suppliers:
      | code      | label     | contributor_count |
      | supplier1 | Supplier1 | 0                 |
      | supplier2 | Supplier2 | 2                 |

  Scenario:
    Given a supplier with code "123" and label "Jessie Pinkman"
    And a supplier with code "456" and label "Walter White"
    When I search on "er wh"
    Then I should have the following suppliers:
      | code | label        |contributor_count |
      | 456  | Walter White | 0                |
