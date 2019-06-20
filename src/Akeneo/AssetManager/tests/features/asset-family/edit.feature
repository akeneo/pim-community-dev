Feature: Edit an asset family
  In order to update the information of an asset family
  As a user
  I want see the details of an asset family and update them

  @acceptance-back
  Scenario: Updating an asset family labels
    Given a valid asset family
    When the user updates the asset family "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the asset family "designer" should be:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Updating when the asset family doesn't have an image
    Given the asset family 'brand' with the label 'en_US' equal to '"Brand"'
    When the user updates the image of the asset family 'brand' with path '"/path/image.jpg"' and filename '"image.jpg"'
    Then the image of the asset family 'brand' should be '"in/memory/files/image.jpg"'

  @acceptance-back
  Scenario: Updating when the asset family has already an image
    Given an image on an asset family 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    When the user updates the image of the asset family 'designer' with path '"/path/image2.jpg"' and filename '"image2.jpg"'
    Then the image of the asset family 'designer' should be '"in/memory/files/image2.jpg"'

  @acceptance-back
  Scenario: Updating an asset family with an empty image
    Given an image on an asset family 'designer' with path '"/path/image.jpg"' and filename '"image.jpg"'
    When the user updates the asset family 'designer' with an empty image
    Then the asset family 'designer' should have an empty image

  @acceptance-back
  Scenario Outline: Updating with an invalid image
    Given a valid asset family
    When the user updates the image of the asset family 'designer' with path '<wrong_path>' and filename '<wrong_filename>'
    Then there should be a validation error on the property 'image' with message '<message>'

    Examples:
      | wrong_path        | wrong_filename | message                              |
      | false             | "image.jpg"    | This value should not be blank.      |
      | 150               | "image.jpg"    | This value should be of type string. |
      | "/path/image.jpg" | false          | This value should not be blank.      |
      | "/path/image.jpg" | 150            | This value should be of type string. |

  @acceptance-front
  Scenario: Updating an asset family labels
    Given a valid asset family
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_family_edit | true |
    When the user updates the asset family "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the asset family "designer" should be:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-front
  Scenario: Cannot update an asset family labels without the locale permission
    Given a valid asset family
    And the user has the following rights:
      | akeneo_assetmanager_asset_family_edit | true |
    Then the label of the asset family "designer" should be read only

  @acceptance-front
  Scenario: Updating an asset family with unexpected backend answer
    Given a valid asset family
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_family_edit | true |
    When the user changes the asset family "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the saved asset family "designer" will be:
      | identifier | labels                                       | image | permission     | attribute_as_label | attribute_as_image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  | {"edit": true} |                    |                    |
    And the user saves the changes
    And the user shouldn't be notified that modification have been made
    And the user should see the saved notification
    And the asset family "designer" should be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: Updating an asset family when the backend answer an error
    Given a valid asset family
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_family_edit | true |
    When the user changes the asset family "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the asset family "designer" save will fail
    And the user saves the changes
    And the user should see the saved notification error

  @acceptance-front
  Scenario: Display updated edit form message
    Given a valid asset family
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_family_edit | true |
    When the user changes the asset family "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user should be notified that modification have been made
    And the saved asset family "designer" will be:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-front
  Scenario: User can't edit an asset family without the good rights
    Given a valid asset family
    And the user does not have any rights
    Then the label of the asset family "designer" should be read only
    And the save button should not be displayed
