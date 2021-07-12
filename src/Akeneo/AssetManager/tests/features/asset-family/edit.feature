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

  @acceptance-back
  Scenario: Updating an asset family to change its attribute as main media
    Given an asset family with a media file attribute "main_picture"
    When the user updates the attribute as main media to be "main_picture"
    Then the attribute as main media should be "main_picture"

  @acceptance-back @error
  Scenario: Cannot update an asset family if the provided attribute as main media does not exist
    Given an asset family with a media file attribute "main_picture"
    When the user updates the attribute as main media to be "toto_picture"
    Then there should be a validation error with message 'The attribute "toto_picture" set as main media for the asset family does not exist'

  @acceptance-back @error
  Scenario: Cannot update an asset family if the provided attribute type is invalid
    Given an asset family with a media file attribute "main_picture"
    When the user updates the attribute as main media to be "label"
    Then there should be a validation error with message 'The attribute set as main media for the asset family should be of type: media_link, media_file'

  @acceptance-back @error
  Scenario: Cannot update an asset family if the provided attribute as main media does not match with the naming convention property
    Given an asset family with a naming convention and a media file attribute "toto_picture"
    When the user updates the attribute as main media to be "toto_picture" without updating the naming convention
    Then there should be a validation error with message 'Property must match the code of the attribute defined as main media'

  # Product link rules
  @acceptance-back
  Scenario: Updating an asset family to set a collection of static rule templates
    Given an empty rule template collection on the asset family 'packshot'
    When the user updates the asset family 'packshot' to set a collection of rule templates
    Then the asset family 'packshot' should have the collection of rule templates

  @acceptance-back @error
  Scenario: Cannot update an asset family if there is no product selections
    When the user updates an asset family "packshot" with an empty product selections
    Then there should be a validation error with message 'You must specify at least one product selection in your product link rule'

  @acceptance-back @error
  Scenario: Cannot update an asset family if there is no product assignment
    When the user updates an asset family "packshot" with an empty product assignment
    Then there should be a validation error with message 'You must specify at least one product assignment in your product link rule'

  @acceptance-back @error
  Scenario: Cannot update an asset family with a collection of rule templates that contains more than 2 items
    Given an empty rule template collection on the asset family 'packshot'
    When the user updates the asset family 'packshot' to set a collection of rule templates having more items than the limit
    Then there should be a validation error with message 'You have reached the limit of 2 product link rules for the "packshot" asset family.'

  @acceptance-back @error
  Scenario: Cannot update an asset family if one of the product link rule is not executable by the rule engine
    When the user updates the asset family 'packshot' with a product link rule not executable by the rule engine
    Then there should be a validation error stating why the rule engine cannot execute the product link rule

  @acceptance-back @error
  Scenario Outline: Cannot update an asset family if a product selection has a filter on a product property with a channel
    Given an empty rule template collection on the asset family 'packshot'
    When the user updates an asset family "packshot" with a product selection field "<product_field>" and channel
    Then there should be a validation error with message '<error_message>'

    Examples:
      | product_field | error_message                                           |
      | categories    | You cannot specify a channel for the field "categories" |
      | family        | You cannot specify a channel for the field "family"     |
      | enable        | You cannot specify a channel for the field "enable"     |

  @acceptance-back @error
  Scenario Outline: Cannot update an asset family if a product selection has a filter on a product property with a locale
    Given an empty rule template collection on the asset family 'packshot'
    When the user updates an asset family "packshot" with a product selection field "<product_field>" and locale
    Then there should be a validation error with message '<error_message>'

    Examples:
      | product_field | error_message                                           |
      | categories    | You cannot specify a locale for the field "categories" |
      | family        | You cannot specify a locale for the field "family"     |
      | enable        | You cannot specify a locale for the field "enable"     |

  # Product selection field
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic product selection field
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a product selection field which references this attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection field referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having a product selection field which references this attribute
    Then there should be a validation error stating that the product selection field does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection field referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having a product selection field which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection field referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having a product selection field which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

  # Product selection value
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic product selection value
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a product selection value which references this text attribute
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic product selection value which references a single option attribute
    Given an asset family with no product link rules and a single option attribute
    When the user updates this asset family with a dynamic product link rule having a product selection value which references this single option attribute
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic product selection value which references a multiple option attribute
    Given an asset family with no product link rules and a multiple option attribute
    When the user updates this asset family with a dynamic product link rule having a product selection value which references this multiple option attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection value referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having a product selection value which references this attribute
    Then there should be a validation error stating that the product selection value does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection value referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having a product selection value which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection value referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having a product selection value which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

  # Product selection channel
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having no product selection channel
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having no product selection channel
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a static product selection channel
    Given an asset family with no product link rules and a channel
    When the user updates this asset family with a product link rule having a product selection channel referencing this channel
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic product selection channel
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a product selection channel referencing this text attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a static product selection channel that does not exist
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having a product selection channel that does not exist
    Then there should be a validation error stating that the product selection channel does not exist

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection channel referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having a product selection channel which references this attribute
    Then there should be a validation error stating that the product selection channel does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection channel referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having a product selection channel which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection channel referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having a product selection channel which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

  # Product selection locale
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having no product selection locale
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having no product selection locale
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a static product selection locale
    Given an asset family with no product link rules and a locale
    When the user updates this asset family with a product link rule having a product selection locale referencing this locale
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic product selection locale
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a product selection locale which references this text attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a static product selection locale that does not exist
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having a product selection locale that does not exist
    Then there should be a validation error stating that the product selection locale does not exist

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection locale referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having a product selection locale which references this attribute
    Then there should be a validation error stating that the product selection locale does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection locale referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having a product selection locale which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic product selection locale referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having a product selection locale which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

  # Product assignment attribute
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic assignment attribute
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a dynamic assignment attribute which references this text attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having an assignment attribute referencing an attribute which is not of the same type of the asset family we are trying to update
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having an assignment attribute which references a product attribute which type does not point to the asset we are trying to update
    Then there should be a validation error stating that this attribute has not the same type of the asset family we are trying to update

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having an assignment attribute referencing an attribute which is does not exist
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having an assignment attribute which references a product attribute which does not exist
    Then there should be a validation error stating that this product attribute does not exist

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having an assignment attribute referencing an attribute which cannot contain an asset
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having an assignment attribute which references a product attribute which cannot contain an asset
    Then there should be a validation error stating that this product attribute cannot contain assets

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment attribute referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having an assignment attribute which references this attribute
    Then there should be a validation error stating that the product selection locale does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment attribute referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having an assignment attribute which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment attribute referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having an assignment attribute which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

  # Product assignment mode
  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link having an unsupported assignment mode
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having "INVALID_MODE" assignment mode
    Then there should be a validation error with message 'Invalid assignment mode "INVALID_MODE" found in a product link rule, expected to be "replace" or "add"'

  @acceptance-back @nominal
  Scenario Outline: Updating an asset family with a product link rule having a replace assignment mode
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having "<mode>" assignment mode
    Then there is an asset family with a product link rule

    Examples:
      | mode    |
      | replace |
      | add     |

  # Product assignment channel
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having no assignment channel
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having no assignment channel
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a static assignment channel
    Given an asset family with no product link rules and a channel
    When the user updates this asset family with a product link rule having an assignment channel referencing this channel
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic assignment channel
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a dynamic assignment channel which references this text attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a static assignment channel that does not exist
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having a assignment channel that does not exist
    Then there should be a validation error stating that the assignment channel does not exist

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment channel referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having an assignment channel which references this attribute
    Then there should be a validation error stating that the product assignment channel does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment channel referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having an assignment channel which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment channel referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having an assignment locale which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

  # Product assignment locale
  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having no assignment locale
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having no assignment locale
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a static assignment locale
    Given an asset family with no product link rules and a locale
    When the user updates this asset family with a product link rule having an assignment locale referencing this locale
    Then there is an asset family with a product link rule

  @acceptance-back @nominal
  Scenario: Updating an asset family with a product link rule having a dynamic assignment locale
    Given an asset family with no product link rules and a text attribute
    When the user updates this asset family with a dynamic product link rule having a dynamic assignment locale which references this text attribute
    Then there is an asset family with a product link rule

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a static assignment locale that does not exist
    Given an asset family with no product link rules
    When the user updates this asset family with a product link rule having an assignment locale that does not exist
    Then there should be a validation error stating that the assignment locale does not exist

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment locale referencing an unsupported attribute type
    Given an asset family with no product link rules and an attribute with a type unsupported for extrapolation
    When the user updates this asset family with a dynamic product link rule having an assignment locale which references this attribute
    Then there should be a validation error stating that the product assignment locale does not support this attribute for extrapolation

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment locale referencing an attribute with one value per channel
    Given an asset family with no product link rules and an attribute with one value per channel
    When the user updates this asset family with a dynamic product link rule having an assignment locale which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per channel

  @acceptance-back @error
  Scenario: Cannot update an asset family with a product link rule having a dynamic assignment locale referencing an attribute with one value per locale
    Given an asset family with no product link rules and an attribute with one value per locale
    When the user updates this asset family with a dynamic product link rule having an assignment locale which references this attribute
    Then there should be a validation error stating that this attribute is not supported for extrapolation because it has one value per locale

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
      | identifier | labels                                       | image | permission     | attribute_as_label | attribute_as_main_media |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  | {"edit": true} |                    |                         |
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
