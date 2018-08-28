Feature: Lists all records of an enriched entity
  In order to see the records of an enriched entity
  As a user
  I want to list all of its records

  @acceptance-front
  Scenario: List all records of an enriched entity
    Given the following enriched entity:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |
    And the following records for the enriched entity "designer":
      | code   | labels                        |
      | starck | {"en_US": "Philippe Starck" } |
      | coco   | {"en_US": "Coco"}             |
    When the user asks for the enriched entity "designer"
    Then the list of records should be:
      | code   |
      | starck |
      | coco   |

  @acceptance-front
  Scenario: Shows an empty page when there are no records for the enriched entity
    Given the following enriched entity:
      | identifier | labels                                | image |
      | brand      | {"en_US": "Brand", "fr_FR": "Marque"} | null  |
    When the user asks for the enriched entity "brand"
    Then the list of records should be empty
