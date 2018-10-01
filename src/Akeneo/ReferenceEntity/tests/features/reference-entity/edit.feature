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
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Updating when the enriched entity doesn't have an image
    Given the enriched entity 'designer' with the label 'en_US' equal to '"Designer"'
    When the user updates the image of the enriched entity 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    Then the image of the enriched entity 'designer' should be '"/path/image.jpg"'

  @acceptance-back
  Scenario: Updating when the enriched entity has already an image
    Given an image on an enriched entity 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    When the user updates the image of the enriched entity 'designer' with path '"/path/image2.jpg"' and filename '"image2.jpg"'
    Then the image of the enriched entity 'designer' should be '"/path/image2.jpg"'

  @acceptance-back
  Scenario: Updating without update the image
    Given an image on an enriched entity 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    When the user updates the enriched entity 'designer' with the label 'en_US' equal to '"Designer"'
    Then the image of the enriched entity 'designer' should be '"/path/image.jpg"'

  @acceptance-back
  Scenario Outline: Updating with an invalid image
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user updates the image of the enriched entity 'designer' with path '<wrong_path>' and filename '<wrong_filename>'
    Then there should be a validation error on the property 'image' with message '<message>'

    Examples:
      | wrong_path        | wrong_filename | message                              |
      | false             | "image.jpg"    | This value should not be blank.      |
      | 150               | "image.jpg"    | This value should be of type string. |
      | "/path/image.jpg" | false          | This value should not be blank.      |
      | "/path/image.jpg" | 150            | This value should be of type string. |

  @acceptance-front
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
    And the user shouldn't be notified that modification have been made
    And the user should see the saved notification
    And the enriched entity "designer" should be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: Updating an enriched entity when the backend answer an error
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the enriched entity "designer" save will fail
    And the user saves the changes
    And the user should see the saved notification error

  @acceptance-front
  Scenario: Display updated edit form message
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
    When the user changes the enriched entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user should be notified that modification have been made
    And the saved enriched entity "designer" will be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |
