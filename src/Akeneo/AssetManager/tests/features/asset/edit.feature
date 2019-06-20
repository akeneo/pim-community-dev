Feature: Edit an asset
  In order to update the information of an asset
  As a user
  I want see the details of an asset and update them

  @acceptance-back
  Scenario: Updating a asset label
    Given an asset family and a asset with french label "My label"
    When the user updates the french label to "My updated label"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the french label "My updated label"

  @acceptance-back
  Scenario: Emptying a asset label
    Given an asset family and a asset with french label "My label"
    When the user empties the french label
    Then there is no exception thrown
    And there is no violations errors
    And the asset should not have a french label

  @acceptance-back
  Scenario: Asset is not valid when enriching a asset label by specifying a not activated locale for the label
    Given an asset family and a asset with french label "My label"
    When the user updates the german label to "My updated label"
    Then there should be a validation error on the property labels with message "The locale "de_DE" is not activated."

  @acceptance-back
  Scenario: Updating a asset default image
    Given a assetFamily and a asset with an image
    When the user updates the asset default image with a valid file
    And the asset should have the new default image

  @acceptance-back
  Scenario: Updating a asset with an empty image
    Given a assetFamily and a asset with an image
    When the user updates the asset default image with an empty image
    And the asset should have an empty image

  # ValuePerChannel / ValuePerLocale
  @acceptance-back
  Scenario: Updating a localizable value of a asset
    Given an asset family with a localizable attribute
    And a asset belonging to this asset family with a value for the french locale
    When the user updates the attribute of the asset for the french locale
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the updated value for this attribute and the french locale

  @acceptance-back
  Scenario: Asset is not valid when enriching a localizable attribute value of a asset without specifying the locale in the value
    Given an asset family with a localizable attribute
    When the user updates the localizable attribute value of the asset without specifying the locale
    Then there should be a validation error on the property text attribute with message "A locale is expected for attribute "name" because it has a value per locale."

  @acceptance-back
  Scenario: Asset is not valid when enriching a not localizable attribute value of a asset by specifying the locale in the value
    Given an asset family with a not localizable attribute
    When the user updates the not localizable attribute value of the asset by specifying the locale
    Then there should be a validation error on the property text attribute with message "A locale is not expected for attribute "name" because it has not a value per locale."

  @acceptance-back
  Scenario: Asset is not valid when enriching a localizable attribute value of a asset by specifying a not activated locale in the value
    Given an asset family with a localizable attribute
    When the user updates the attribute value of the asset by specifying a not activated locale
    Then there should be a validation error on the property text attribute with message "The locale "de_DE" is not activated."

  @acceptance-back
  Scenario: Updating a scopable value of a asset
    Given an asset family with a scopable attribute
    And a asset belonging to this asset family with a value for the ecommerce channel
    When the user updates the attribute of the asset for the ecommerce channel
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the updated value for this attribute and the ecommerce channel

  @acceptance-back
  Scenario: Asset is not valid when enriching a scopable attribute value without specifying the channel in the value
    Given an asset family with a scopable attribute
    And a asset belonging to this asset family with a value for the ecommerce channel
    When the user enriches a scopable attribute value of a asset without specifying the channel
    Then there should be a validation error on the property text attribute with message "A channel is expected for attribute "name" because it has a value per channel."

  @acceptance-back
  Scenario: Asset is not valid when enriching a not scopable attribute value by specifying the channel in the value
    Given an asset family with a not scopable attribute
    And a asset belonging to this asset family with a value for the not scopable attribute
    When the user updates the not scopable attribute of the asset by specifying a channel
    Then there should be a validation error on the property text attribute with message "A channel is not expected for attribute "name" because it has not a value per channel."

  @acceptance-back
  Scenario: Asset is not valid when enriching a scopable attribute value by specifying an unknown channel in the value
    Given an asset family with a scopable attribute
    And a asset belonging to this asset family with a value for the ecommerce channel
    When the user updates the attribute value of the asset by specifying an unknown channel
    Then there should be a validation error on the property text attribute with message "The channel "unknown_channel" does not exist."

  @acceptance-back
  Scenario: Updating a scopable and localizable value of a asset
    Given an asset family with a scopable and localizable attribute
    And a asset belonging to this asset family with a value for the ecommerce channel and french locale
    When the user updates the attribute of the asset for the ecommerce channel and french locale
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the updated value for this attribute and the ecommerce channel and the french locale

  @acceptance-back
  Scenario: Asset is not valid when enriching a scopable and localizable attribute value by specifying a locale not activated for the channel in the value
    Given an asset family with a scopable and localizable attribute
    When the user updates the attribute value of the asset by specifying a locale not activated for the ecommerce channel
    Then there should be a validation error on the property text attribute with message "The locale "de_DE" is not activated for the channel "ecommerce"."

  # Text
  @acceptance-back
  Scenario: Updating the text value of a asset
    Given an asset family with a text attribute
    And a asset belonging to this asset family with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the asset to "Stark"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the text value "Stark" for this attribute

  @acceptance-back
  Scenario: Emptying a text value
    Given an asset family with a text attribute
    And a asset belonging to this asset family with a value of "Philippe stark" for the text attribute
    When the user empties the text attribute of the asset
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have an empty value for this attribute

  @acceptance-back
  Scenario: Setting a text value to empty string empties this value
    Given an asset family with a text attribute
    And a asset belonging to this asset family with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the asset to ""
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have an empty value for this attribute

  @acceptance-back
  Scenario: Updating the text value of a asset with an invalid value type
    Given an asset family with a text attribute
    And a asset belonging to this asset family with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the asset to an invalid value type
    Then an exception is thrown with message "There was no factory found to create the edit asset value command of the attribute "name_designer_fingerprint""

  @acceptance-back
  Scenario: Updating the text value with more characters than the attribute's max length
    Given an asset family with a text attribute with max length 10
    And a asset belonging to this asset family with a value of "Philippe" for the text attribute
    When the user updates the text attribute of the asset to "Philippe Starck, né le 18 janvier 1949 à Paris"
    Then there should be a validation error on the property text attribute with message "This value is too long. It should have 10 characters or less."

  @acceptance-back
  Scenario: Updating the text value with less characters than the attribute's max length
    Given an asset family with a text attribute with max length 10
    And a asset belonging to this asset family with a value of "Philippe" for the text attribute
    When the user updates the text attribute of the asset to "Didier"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the text value "Didier" for this attribute

  @acceptance-back
  Scenario: Updating an email with an invalid email value
    Given an asset family with a text attribute with an email validation rule
    And a asset belonging to this asset family with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the asset to "Hello my name is jean-pierre."
    Then there should be a validation error on the property text attribute with message "This value is not a valid email address."

  @acceptance-back
  Scenario: Updating an email with an valid email value
    Given an asset family with a text attribute with an email validation rule
    And a asset belonging to this asset family with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the asset to "didier@dummy.com"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the text value "didier@dummy.com" for this attribute

  @acceptance-back
  Scenario: Updating an url with an invalid url value
    Given an asset family with a text attribute with an url validation rule
    And a asset belonging to this asset family with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the asset to "htt://akeneo.com/"
    Then there should be a validation error on the property text attribute with message "This value is not a valid URL."

  @acceptance-back
  Scenario: Updating an url with an valid url value
    Given an asset family with a text attribute with an url validation rule
    And a asset belonging to this asset family with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the asset to "http://akeneo.com/"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the text value "http://akeneo.com/" for this attribute

  @acceptance-back
  Scenario: Updating a text with regular expression with an incompatible value
    Given an asset family with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a asset belonging to this asset family with a value of "15|25" for the text attribute
    When the user updates the text attribute of the asset to "15-25"
    Then there should be a validation error on the property text attribute with message "The text is incompatible with the regular expression "/\d+\|\d+/""

  @acceptance-back
  Scenario: Updating a text with regular expression with an compatible value
    Given an asset family with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a asset belonging to this asset family with a value of "15|25" for the text attribute
    When the user updates the text attribute of the asset to "15|25"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the text value "15|25" for this attribute

  # Image
  @acceptance-back
  Scenario: Updating the image value of a asset by uploading a new one
    Given an asset family with an image attribute
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with a valid uploaded file
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the valid image for this attribute

  @acceptance-back
  Scenario: Emptying the file value of a asset
    Given an asset family with an image attribute
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user removes an image from the asset for this attribute
    Then there is no exception thrown
    And there is no violations errors
    And the asset should not have any image for this attribute

  @acceptance-back
  Scenario: Updating the image value of a asset with an image that does not exist
    Given an asset family with an image attribute
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset to an image that does not exist
    Then there should be a validation error on the property image attribute with message "The file "/files/not_found.png" was not found."

  @acceptance-back
  Scenario: Updating the image value of a asset with an uploaded file having an extension not allowed
    Given an asset family with an image attribute allowing only files with extension png
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with an uploaded gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a asset with an stored file having an extension not allowed
    Given an asset family with an image attribute allowing only files with extension png
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with a stored gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a asset with a file having an extension not allowed
    Given an asset family with an image attribute allowing only files with extension png
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with an uploaded png file
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the valid image for this attribute

  @acceptance-back
  Scenario: Updating the image value of a asset with an uploaded file bigger than the limit
    Given an asset family with an image attribute having a max file size of 15ko
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with a bigger uploaded file than the limit
    Then there should be a validation error on the property image attribute with message "The file exceeds the max file size set for the attribute."

  @acceptance-back
  Scenario: Updating the image value of a asset with a stored file bigger than the limit
    Given an asset family with an image attribute having a max file size of 15ko
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with a bigger stored file than the limit
    Then there should be a validation error on the property image attribute with message "The file exceeds the max file size set for the attribute."

  @acceptance-back
  Scenario: Updating the image value of a asset with an invalid mime type
    Given an asset family with an image attribute allowing only files with extension png
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with a stored gif file which is a denied extension
    Then there should be a validation error on the property image attribute with message '".gif" files are not allowed for this attribute. Allowed extensions are: png'

  @acceptance-back
  Scenario: Updating the image value of a asset with a file smaller than the limit
    Given an asset family with an image attribute having a max file size of 15ko
    And a asset belonging to this asset family with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the asset with a smaller file than the limit
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the valid image for this attribute

  # Asset value
  @acceptance-back
  Scenario: Updating the asset value of a asset
    Given an asset family with a asset attribute
    And a asset belonging to this asset family with a value of "ikea" for the asset attribute
    When the user updates the asset attribute of the asset to "made"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the asset value "made" for this attribute

  @acceptance-back
  Scenario: Updating the asset value of a asset with an invalid asset value
    Given an asset family with a asset attribute
    And a asset belonging to this asset family with a value of "ikea" for the asset attribute
    When the user updates the asset attribute of the asset to an invalid asset value
    Then an exception is thrown with message "There was no factory found to create the edit asset value command of the attribute "brand_linked_designer_fingerprint""

  @acceptance-back
  Scenario: Updating the asset value of a asset with a non-existent asset
    Given an asset family with a asset attribute
    And a asset belonging to this asset family with a value of "ikea" for the asset attribute
    When the user tries to update the asset attribute of the asset with an unknown value
    Then there should be a validation error on the property asset attribute with message "The asset "unknown_brand" was not found."

  # Asset collection value
  @acceptance-back
  Scenario: Updating the asset collection value of a asset
    Given an asset family with a asset collection attribute
    And a asset belonging to this asset family with values of "ikea,made" for the asset collection attribute
    When the user updates the asset collection attribute of the asset to "stork,cogip"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the asset collection value "stork,cogip" for this attribute

  @acceptance-back
  Scenario: Updating the asset collection value of a asset with an invalid asset value
    Given an asset family with a asset collection attribute
    And a asset belonging to this asset family with values of "ikea,made" for the asset collection attribute
    When the user updates the asset collection attribute of the asset to an invalid asset value
    Then an exception is thrown with message "There was no factory found to create the edit asset value command of the attribute "brand_linked_designer_fingerprint""

  @acceptance-back
  Scenario: Updating the asset collection value of a asset with non-existent assets
    Given an asset family with a asset collection attribute
    And a asset belonging to this asset family with values of "ikea,made" for the asset collection attribute
    When the user updates the asset collection attribute of the asset with unknown values
    Then there should be a validation error on the property asset attribute with message "The assets "unknown_brand,wrong_brand" were not found."

  # Option value
  @acceptance-back
  Scenario: Updating the option value of a asset
    Given an asset family with an option attribute
    And a asset belonging to this asset family with values of "green" for the option attribute
    When the user updates the option attribute of the asset to "red"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the option value "red" for this attribute

  @acceptance-back
  Scenario: Updating the option value of a asset with non-existent option
    Given an asset family with an option attribute
    And a asset belonging to this asset family with values of "green" for the option attribute
    When the user updates the option attribute of the asset to "blue"
    Then there should be a validation error on the property option attribute with message "The option with code "blue" does not exist for this attribute"
    And the asset should have the option value "green" for this attribute

  # Option collection value
  @acceptance-back
  Scenario: Updating the option collection value of a asset
    Given an asset family with an option collection attribute
    And a asset belonging to this asset family with values of "vodka, whisky" for the option collection attribute
    When the user updates the option collection attribute of the asset to "vodka, rhum"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the option collection value "vodka, rhum" for this attribute

  @acceptance-back
  Scenario: Updating the option collection value of a asset with non-existent option
    Given an asset family with an option collection attribute
    And a asset belonging to this asset family with values of "vodka, whisky" for the option collection attribute
    When the user updates the option collection attribute of the asset to "vodka, water"
    Then there should be a validation error on the property option collection attribute with message "The following option codes don't exist for this attribute : "water""
    And the asset should have the option collection value "vodka, whisky" for this attribute

  # Number value
  @acceptance-back
  Scenario Outline: Updating the number value of a asset with decimals
    Given an asset family with a number attribute
    And a asset belonging to this asset family with values of "33" for the number attribute
    When the user updates the number attribute of the asset to "<new_value>"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the number value "<new_value>" for this attribute

    Examples:
      | new_value |
      | 0         |
      | 59        |
      | -159      |
      | 59.12     |
      | -0.5      |

  @acceptance-back
  Scenario: Updating the number value of a asset with decimals with an invalid value
    Given an asset family with a number attribute
    And a asset belonging to this asset family with values of "33" for the number attribute
    When the user updates the number attribute of the asset to "aze"
    Then there should be a validation error on the number value with message "This field should be a numeric value with the right decimal separator"
    And the asset should have the number value "33" for this attribute

  @acceptance-back
  Scenario Outline: Updating the number value of a asset with no decimal
    Given an asset family with a number attribute with no decimal value
    And a asset belonging to this asset family with values of "33" for the number attribute
    When the user updates the number attribute of the asset to "<new_value>"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the number value "<new_value>" for this attribute

    Examples:
      | new_value |
      | 0         |
      | 59        |
      | -159      |

  @acceptance-back
  Scenario Outline: Updating the number value of a asset with a forbidden decimal value
    Given an asset family with a number attribute with no decimal value
    And a asset belonging to this asset family with values of "10" for the number attribute
    When the user updates the number attribute of the asset to "<invalid_value>"
    Then there should be a validation error on the number value with message "<error_message>"
    And the asset should have the number value "10" for this attribute

    Examples:
      | invalid_value | error_message                   |
      | 9.99          | This field should be an integer |
      | abc           | This field should be an integer |

  @acceptance-back
  Scenario: Updating the number value with a number with the minimum number allowed
    Given an asset family with a number attribute with min "-10" and max "10"
    And a asset belonging to this asset family with values of "0" for the number attribute
    When the user updates the number attribute of the asset to "-10"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the number value "-10" for this attribute

  @acceptance-back
  Scenario: Updating the number value with a number with the maximum number allowed
    Given an asset family with a number attribute with min "-10" and max "10"
    And a asset belonging to this asset family with values of "0" for the number attribute
    When the user updates the number attribute of the asset to "10"
    Then there is no exception thrown
    And there is no violations errors
    And the asset should have the number value "10" for this attribute

  @acceptance-back
  Scenario: Updating the number value with a number lower than the minimum allowed
    Given an asset family with a number attribute with min "-10" and max "10"
    And a asset belonging to this asset family with values of "0" for the number attribute
    When the user updates the number attribute of the asset to "-25"
    Then there should be a validation error on the number value with message "This value should be "-10" or more."
    And the asset should have the number value "0" for this attribute

  @acceptance-back
  Scenario: Updating the number value with a number lower than the minimum allowed
    Given an asset family with a number attribute with min "-10" and max "10"
    And a asset belonging to this asset family with values of "0" for the number attribute
    When the user updates the number attribute of the asset to "25"
    Then there should be a validation error on the number value with message "This value should be "10" or less."
    And the asset should have the number value "0" for this attribute

  @acceptance-back
  Scenario: Updating the number value with an integer too long
    Given an asset family with a number attribute with no decimal value
    And a asset belonging to this asset family
    When the user updates the number value with an integer too long
    Then there should be a validation error on the number value with message "This integer is too big"

  @acceptance-back
  Scenario: Updating an url value
    Given an asset family with an url attribute and a asset belonging to this asset family
    When the user updates the url value of the asset
    Then the asset should have the url value for this attribute

  @acceptance-front
  Scenario: Updating a asset details
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: User can't update a asset details without the edit rights
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | false |
    Then the user cannot save the asset

  @acceptance-front
  Scenario: Updating a asset with a simple text value
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a simple text value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a asset with an invalid simple text value
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with an invalid simple text value
    Then the user should see the validation error on the edit page : "This value is not a valid URL."

  @acceptance-front
  Scenario: User can't update a simple text value without the edit rights
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | false |
    Then the user cannot update the simple text value

  @acceptance-front
  Scenario: Updating a asset with a simple option value
    Given a valid asset with an option attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a simple option value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a asset with an invalid simple option value
    Given a valid asset with an option attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with an invalid simple option value
    Then the user should see the validation error on the edit page : "The option with code \"red\" does not exist for this attribute"

  @acceptance-front
  Scenario: User can't update a simple option value without the edit rights
    Given a valid asset with an option attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | false |
    Then the user cannot update the simple option value

  @acceptance-front
  Scenario: Updating a asset with a multiple option value
    Given a valid asset with an option collection attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a multiple option value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a asset with an invalid multiple option value
    Given a valid asset with an option collection attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with an invalid multiple option value
    Then the user should see the validation error on the edit page : "The option with code \"red\" does not exist for this attribute"

  @acceptance-front
  Scenario: User can't update a multiple option value without the edit rights
    Given a valid asset with an option collection attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | false |
    Then the user cannot update the multiple option value

  @acceptance-front
  Scenario: Display bullet point for the completeness when a required field isn't filled
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user ask for the asset
    Then the user should see a completeness bullet point on the required field: "website"
    When the user fill the "website" field with: "http://the-website.com"
    Then the user should not see a completeness bullet point on the required field: "website"

  @acceptance-front
  Scenario: Display completeness percentage on a asset with required fields
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user ask for the asset
    Then the user should see the completeness percentage with a value of "0%"
    When the user fill the "website" field with: "http://the-website.com"
    Then the user should see the completeness percentage with a value of "100%"

  @acceptance-front
  Scenario: Updating a asset with a single asset linked
    Given a valid asset with an asset family single link attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a single asset linked
    # Then the user should see a success message on the edit page #Erratic test

  @acceptance-front
  Scenario: User can't update a single asset linked value without the edit rights
    Given a valid asset with an asset family single link attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | false |
    Then the user cannot update the single asset linked value

  @acceptance-front
  Scenario: Updating a asset with a multiple asset linked
    Given a valid asset with an asset family multiple link attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a multiple asset linked
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: User can't update a multiple asset linked value without the edit rights
    Given a valid asset with an asset family multiple link attribute
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | false |
    Then the user cannot update the multiple asset linked value

  @acceptance-front
  Scenario: Updating a asset with a number value
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a number value
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Updating a asset with a number value out of range
    Given a valid asset
    And the user has the locale permission to edit the asset
    And the user has the following rights:
      | akeneo_assetmanager_asset_edit | true |
    When the user saves the valid asset with a number out of range
    Then the user should see the validation error on the edit page : 'This value should be "-10" or more.'


#  Todo : Fix random call for the preview image
#  @acceptance-front
#  Scenario: Updating a asset with an image value
#    Given a valid asset
#    When the user updates the valid asset with an image value
#    Then the user should see a success message after the update asset
#
#  @acceptance-front
#  Scenario: Updating a asset with an invalid image value
#    Given a valid asset
#    When the user saves the valid asset with an invalid image value
#    Then the user should see the validation error after the update asset : "This value is not a valid URL."
