Feature: Create an enriched entity
  In order to create an enriched entity
  As a user
  I want create an enriched entity

  @acceptance-back @acceptance-front
  Scenario: Creating an enriched entity
    When the user creates an enriched entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then there is an enriched entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Creating an enriched entity with no labels
    When the user creates an enriched entity "designer" with:
      | labels | {} |
    Then there is an enriched entity "designer" with:
      | identifier | labels |
      | designer   | {}     |

  @acceptance-back
  Scenario: Cannot create an enriched entity with invalid identifier
    When the user creates an enriched entity "invalid/identifier" with:
      | labels | {} |
    Then an exception is thrown with message "Enriched Entity identifier may contain only letters, numbers and underscores"
    And there should be no enriched entity
