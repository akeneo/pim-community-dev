Feature: Delete an enriched entity
  In order to keep my enriched entities up to date
  As a user
  I want to delete an enriched entity

  Background:
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-back
  Scenario: Delete an enriched entity
    When the user deletes the enriched entity "designer"
    Then there should be no enriched entity "designer"
