Feature: Lists all attributes related to an asset family
  In order to see the structure of an asset family
  As a user
  I want to list all of its attributes

  @acceptance-front
  Scenario: List all attributes of an asset family
    Given a valid asset family
    And the following attributes for the asset family "designer":
      | code           | type              | labels                                  |
      | name           | text              | {"en_US": "Name", "fr_FR": "Name"}      |
      | bio            | text              | {"en_US": "Bio", "fr_FR": "Biographie"} |
      | portrait       | image             | {"en_US": "Portrait", "fr_FR": "Image"} |
      | favorite_color | option            | {"en_US": "Favorite Color"}             |
      | colors         | option_collection | {"en_US": "Colors"}                     |
    When the user asks for the asset family "designer"
    Then there should be the following attributes:
      | code           | type              |
      | name           | text              |
      | bio            | text              |
      | portrait       | image             |
      | favorite_color | option            |
      | colors         | option_collection |

  @acceptance-front
  Scenario: Shows an empty page when there are no attributes for the asset family
    Given a valid brand asset family
    When the user asks for the asset family "brand"
    Then the list of attributes should be empty
