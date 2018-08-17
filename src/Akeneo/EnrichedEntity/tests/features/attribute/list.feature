Feature: Lists all attributes related to an enriched entity
  In order to see the structure of an enriched entity
  As a user
  I want to list all of its attributes

  @acceptance-front
  Scenario: List all attributes of an enriched entity
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    And the following attributes for the enriched entity "designer":
      | code     | type  | labels                                  |
      | name     | text  | {"en_US": "Name", "fr_FR": "Name"}      |
      | bio      | text  | {"en_US": "Bio", "fr_FR": "Biographie"} |
      | portrait | image | {"en_US": "Portrait", "fr_FR": "Image"} |
    When the user asks for the enriched entity "designer"
    Then the list of attributes should be:
      | code     | type  |
      | name     | text  |
      | bio      | text  |
      | portrait | image |

  @acceptance-front
  Scenario: Shows an empty page when there are no attributes for the enriched entity
    Given the following enriched entity:
      | identifier | labels                                |
      | brand      | {"en_US": "Brand", "fr_FR": "Marque"} |
    When the user asks for the enriched entity "brand"
    Then the list of attributes should be empty
