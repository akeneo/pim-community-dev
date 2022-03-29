Feature: Onboarder Serenity - Suppliers management - edit a supplier

  Scenario: Edit a supplier
    Given a supplier with code "supplier1" and label "Supplier1" and "2" contributors
    When I update the supplier "supplier1" with:
      | label          | contributors                        |
      | The Supplier 1 | email1@akeneo.com;email3@akeneo.com |
    Then I should have the following supplier:
      | code      | label          | contributors                        |
      | supplier1 | The Supplier 1 | email1@akeneo.com;email3@akeneo.com |

  Scenario: Edit a supplier - remove its contributors
    Given a supplier with code "supplier1" and label "Supplier1" and "2" contributors
    When I update the supplier "supplier1" with:
      | label          | contributors |
      | The Supplier 1 |              |
    Then I should have the following supplier:
      | code      | label          | contributors |
      | supplier1 | The Supplier 1 |              |
