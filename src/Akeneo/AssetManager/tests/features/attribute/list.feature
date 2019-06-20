Feature: Lists all attributes related to a reference entity
  In order to see the structure of a reference entity
  As a user
  I want to list all of its attributes

  @acceptance-front
  Scenario: List all attributes of a reference entity
    Given a valid reference entity
    And the following attributes for the reference entity "designer":
      | code           | type              | labels                                  |
      | name           | text              | {"en_US": "Name", "fr_FR": "Name"}      |
      | bio            | text              | {"en_US": "Bio", "fr_FR": "Biographie"} |
      | portrait       | image             | {"en_US": "Portrait", "fr_FR": "Image"} |
      | favorite_color | option            | {"en_US": "Favorite Color"}             |
      | colors         | option_collection | {"en_US": "Colors"}                     |
    When the user asks for the reference entity "designer"
    Then there should be the following attributes:
      | code           | type              |
      | name           | text              |
      | bio            | text              |
      | portrait       | image             |
      | favorite_color | option            |
      | colors         | option_collection |

  @acceptance-front
  Scenario: Shows an empty page when there are no attributes for the reference entity
    Given a valid brand reference entity
    When the user asks for the reference entity "brand"
    Then the list of attributes should be empty
