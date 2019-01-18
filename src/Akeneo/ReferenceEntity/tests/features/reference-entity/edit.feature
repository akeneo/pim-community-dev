Feature: Edit a reference entity
  In order to update the information of a reference entity
  As a user
  I want see the details of a reference entity and update them

  @acceptance-back
  Scenario: Updating a reference entity labels
    Given a valid reference entity
    When the user updates the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the reference entity "designer" should be:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Updating when the reference entity doesn't have an image
    Given the reference entity 'brand' with the label 'en_US' equal to '"Brand"'
    When the user updates the image of the reference entity 'brand' with path '"/path/image.jpg"' and filename '"image.jpg"'
    Then the image of the reference entity 'brand' should be '"in/memory/files/image.jpg"'

  @acceptance-back
  Scenario: Updating when the reference entity has already an image
    Given an image on a reference entity 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    When the user updates the image of the reference entity 'designer' with path '"/path/image2.jpg"' and filename '"image2.jpg"'
    Then the image of the reference entity 'designer' should be '"in/memory/files/image2.jpg"'

  @acceptance-back
  Scenario: Updating a reference entity with an empty image
    Given an image on a reference entity 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    When the user updates the reference entity 'designer' with an empty image
    Then the reference entity 'designer' should have an empty image

  @acceptance-back
  Scenario Outline: Updating with an invalid image
    Given a valid reference entity
    When the user updates the image of the reference entity 'designer' with path '<wrong_path>' and filename '<wrong_filename>'
    Then there should be a validation error on the property 'image' with message '<message>'

    Examples:
      | wrong_path        | wrong_filename | message                              |
      | false             | "image.jpg"    | This value should not be blank.      |
      | 150               | "image.jpg"    | This value should be of type string. |
      | "/path/image.jpg" | false          | This value should not be blank.      |
      | "/path/image.jpg" | 150            | This value should be of type string. |

  @acceptance-front
  Scenario: Updating a reference entity labels
    Given a valid reference entity
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit | true |
    When the user updates the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the reference entity "designer" should be:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-front
  Scenario: Updating a reference entity with unexpected backend answer
    Given a valid reference entity
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit | true |
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the saved reference entity "designer" will be:
      | identifier | labels                                       | image | permission     |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  | {"edit": true} |
    And the user saves the changes
    And the user shouldn't be notified that modification have been made
    And the user should see the saved notification
    And the reference entity "designer" should be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: Updating a reference entity when the backend answer an error
    Given a valid reference entity
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit | true |
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the reference entity "designer" save will fail
    And the user saves the changes
    And the user should see the saved notification error

  @acceptance-front
  Scenario: Display updated edit form message
    Given a valid reference entity
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_reference_entity_edit | true |
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user should be notified that modification have been made
    And the saved reference entity "designer" will be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: User can't edit a reference entity without the good rights
    Given a valid reference entity
    And the user does not have any rights
    Then the label of the reference entity "designer" should be read only
    And the save button should not be displayed
