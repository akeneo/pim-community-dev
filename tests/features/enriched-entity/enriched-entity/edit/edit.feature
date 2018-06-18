Feature: Edit an enriched entity
  In order to update the information of an enriched entity
  As a user
  I want see the details of an enriched entity and update them

  @acceptance-back
  Scenario: Updating an enriched entity labels
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user updates the enriched entity "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Styliste"} |
    Then the enriched entity "designer" should be:
      | identifier | labels                                     |
      | designer   | {"en_US": "Designer", "fr_FR": "Styliste"} |
