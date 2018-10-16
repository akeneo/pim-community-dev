Feature: Lists all attributes related to an reference entity
  In order to see the structure of an reference entity
  As a user
  I want to list all of its attributes

  @acceptance-front
  Scenario: List all attributes of an reference entity
    Given the following reference entity:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |
    And the following attributes for the reference entity "designer":
      | code     | type  | labels                                  |
      | name     | text  | {"en_US": "Name", "fr_FR": "Name"}      |
      | bio      | text  | {"en_US": "Bio", "fr_FR": "Biographie"} |
      | portrait | image | {"en_US": "Portrait", "fr_FR": "Image"} |
    When the user asks for the reference entity "designer"
    Then there should be the following attributes:
      | code     | type  |
      | name     | text  |
      | bio      | text  |
      | portrait | image |

  @acceptance-front
  Scenario: Shows an empty page when there are no attributes for the reference entity
    Given the following reference entity:
      | identifier | labels                                | image |
      | brand      | {"en_US": "Brand", "fr_FR": "Marque"} | null  |
    And the following attributes for the reference entity "brand":
      | code     | type  | labels                          |
    When the user asks for the reference entity "brand"
    Then the list of attributes should be empty
