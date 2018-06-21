Feature: Edit an enriched entity
  In order to update the information of an enriched entity
  As a user
  I want see the details of an enriched entity and update them

  @acceptance-back @acceptance-front
  Scenario: Updating an enriched entity labels
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user updates the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the enriched entity "designer" should be:
      | identifier | labels                                      |
      | designer   | {"en_US": "Stylist", "fr_FR": "Concepteur"} |

  #@acceptance-front
  Scenario: Updating an enriched entity with unexpected backend answer
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the saved enriched entity "designer" will be:
      | identifier | labels                                      |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    And the user saves the changes
    And the enriched entity "designer" should be:
      | identifier | labels                                      |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
