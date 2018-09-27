Feature: Edit an record
  In order to update the information of an record
  As a user
  I want see the details of an record and update them

  # ValuePerChannel / ValuePerLocale
  @acceptance-back
  Scenario: Updating a localizable value of a record
    Given an enriched entity with a localizable attribute
    And a record belonging to this enriched entity with a value for the french locale
    When the user updates the attribute of the record for the french locale
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the french locale

  @acceptance-back
  Scenario: Updating a scopable value of a record
    Given an enriched entity with a scopable attribute
    And a record belonging to this enriched entity with a value for the ecommerce channel
    When the user updates the attribute of the record for the ecommerce channel
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the updated value for this attribute and the ecommerce channel

  @acceptance-back
  Scenario: Updating a scopable value of a record with an invalid channel
    Given an enriched entity with a scopable attribute
    And a record belonging to this enriched entity with a value for the ecommerce channel
    When the user updates the attribute of the record for an invalid channel
    Then there should be a validation error on the property text attribute with message "This value should be of type boolean."

  @acceptance-back
  Scenario: Updating a scopable value of a record with an unknown channel
    Given an enriched entity with a scopable attribute
    And a record belonging to this enriched entity with a value for the ecommerce channel
    When the user updates the attribute of the record for an unknown channel
    Then there should be a validation error on the property text attribute with message "This value should be of type boolean."

#  Todo: Scenario to activate for the import,exports/API
#  @acceptance-back
#  Scenario: Updating a scopable and localizable value of a record
#    Given an enriched entity with a scopable and localizable attribute
#    And a record belonging to this enriched entity with a value for the ecommerce channel and french locale
#    When the user updates the attribute of the record for the ecommerce channel and french locale
#    Then there is no exception thrown
#    And there is no violations errors
#    And the record should have the updated value for this attribute and the ecommerce channel and the french locale

  # Text
  @acceptance-back
  Scenario: Updating the text value of a record
    Given an enriched entity with a text attribute
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Stark"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the text value "Stark" for this attribute

  @acceptance-back
  Scenario: Emptying a text value
    Given an enriched entity with a text attribute
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user empties the text attribute of the record
    Then there is no exception thrown
    And there is no violations errors
    And the record should have an empty value for this attribute

  @acceptance-back
  Scenario: Setting a text value to empty string empties this value
    Given an enriched entity with a text attribute
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to ""
    Then there is no exception thrown
    And there is no violations errors
    And the record should have an empty value for this attribute

  @acceptance-back
  Scenario: Updating the text value of a record with an invalid value type
    Given an enriched entity with a text attribute
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to an invalid value type
    Then there should be a validation error on the property text attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the text value with more characters than the attribute's max length
    Given an enriched entity with a text attribute with max length 10
    And a record belonging to this enriched entity with a value of "Philippe stark" for the text attribute
    When the user updates the text attribute of the record to "Philippe Starck, né le 18 janvier 1949 à Paris"
    Then there should be a validation error on the property text attribute with message "This value is too long. It should have 10 characters or less."

  @acceptance-back
  Scenario: Updating an email with an invalid email value
    Given an enriched entity with a text attribute with an email validation rule
    And a record belonging to this enriched entity with a value of "jean-pierre@dummy.com" for the text attribute
    When the user updates the text attribute of the record to "Hello my name is jean-pierre."
    Then there should be a validation error on the property text attribute with message "This value is not a valid email address."

  @acceptance-back
  Scenario: Updating an url with an invalid url value
    Given an enriched entity with a text attribute with an url validation rule
    And a record belonging to this enriched entity with a value of "https://www.akeneo.com/" for the text attribute
    When the user updates the text attribute of the record to "My website is 'https://www.akeneo.com/'"
    Then there should be a validation error on the property text attribute with message "This value is not a valid URL."

  @acceptance-back
  Scenario: Updating a text with regular expression with an incompatible value
    Given an enriched entity with a text attribute with a regular expression validation rule like "/\d+\|\d+/"
    And a record belonging to this enriched entity with a value of "15|25" for the text attribute
    When the user updates the text attribute of the record to "15-25"
    Then there should be a validation error on the property text attribute with message "This value is not valid."

  # Image
  @acceptance-back
  Scenario: Updating the image value of a record
    Given an enriched entity with an image attribute
    And a record belonging to this enriched entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to "updated_picture.jpeg"
    Then there is no exception thrown
    And there is no violations errors
    And the record should have the image "updated_picture.jpeg" for this attribute

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid file path
    Given an enriched entity with an image attribute
    And a record belonging to this enriched entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid file path
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with an invalid originale filename
    Given an enriched entity with an image attribute
    And a record belonging to this enriched entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to an invalid file name
    Then there should be a validation error on the property image attribute with message "This value should be of type string."

  @acceptance-back
  Scenario: Updating the image value of a record with a file having an extension not allowed
    Given an enriched entity with an image attribute
    And a record belonging to this enriched entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record to "updated_picture.xrgife"
    Then there should be a validation error on the property image attribute with message "invalid regex"

  @acceptance-back
  Scenario: Updating the image value of a record with a file bigger than the limit
    Given an enriched entity with an image attribute having a max file size of 1MB
    And a record belonging to this enriched entity with the file "picture.jpeg" for the image attribute
    When the user updates the image attribute of the record with a bigger file than the limit
    Then there should be a validation error on the property image attribute with message "Max size invalid"

  @acceptance-front
  Scenario: Updating a record labels
    Given a valid record
    When the user ask for the record
    Then the record should be:
      | labels                                    |
      | {"en_US": "", "fr_FR": "Philippe Starck"} |
