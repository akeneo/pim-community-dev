Feature: Import assets via files
  In order to create and update some assets
  As a user
  I want to import some assets via files

  Background:
    Given an asset family "designer"
    And the 'ecommerce,mobile' channels with 'en_US,fr_FR' locales
    And the user creates a text attribute "name" linked to the asset family "designer" with:
      | value_per_channel | value_per_locale | max_length | order |
      | false             | false            | 200        | 1     |
    And the user creates a text attribute "scopable_text" linked to the asset family "designer" with:
      | value_per_channel | value_per_locale | order |
      | true              | false            | 2     |
    And the user creates a text attribute "localizable_text" linked to the asset family "designer" with:
      | value_per_channel | value_per_locale | order |
      | false             | true             | 3     |
    And the user creates a text attribute "scopable_and_localizable_text" linked to the asset family "designer" with:
      | value_per_channel | value_per_locale | order |
      | true              | true             | 4     |
    And the user creates a text attribute "name_with_validation" linked to the asset family "designer" with:
      | value_per_channel | value_per_locale | max_length | order |
      | false             | false            | 20         | 5     |
    And the user creates a number attribute "scopable_number" to the asset family "designer" with:
      | value_per_channel | value_per_locale | order |
      | true              | false            | 6     |
    And the user creates an option attribute "localizable_color" with:
      | value_per_channel | value_per_locale | order |
      | false             | true             | 7     |
    And the "localizable_color" option attribute contains "red,blue,green" options
    And the user creates an option collection attribute "multiple_colors" with:
      | value_per_channel | value_per_locale | order |
      | false             | false            | 8     |
    And the "multiple_colors" options attribute contains "red,blue,green" options
    And the user creates a media file attribute "picture" linked to the asset family "designer" with:
      | value_per_channel | value_per_locale | order |
      | false             | false            | 9     |
    And an asset belonging to this asset family with a value of "Philippe Stark" for the text attribute

  @acceptance-back
  Scenario: Import some assets using a valid CSV file
    When the user imports a valid CSV file
    Then there is no exception thrown
    And there is no warning thrown
    And there is a asset with:
      | code  | entity_identifier | labels                                           |
      | stark | designer          | {"en_US": "New label", "fr_FR": "Nouveau label"} |
    And there is a asset with:
      | code  | entity_identifier | labels                                           |
      | ikea  | designer          | {"en_US": "Ikea shop", "fr_FR": "Magasin Ikea"}  |
    And there is a asset with:
      | code  | entity_identifier | labels                               |
      | 12345 | designer          | {"en_US": "12345", "fr_FR": "12345"} |
    And the value of the unlocalized unscoped name of the 'stark' asset in 'designer' asset family is '"Stark"'
    And the value of the unlocalized ecommerce scopable_number of the 'stark' asset in 'designer' asset family is '"10"'
    And the value of the en_US unscoped localizable_color of the 'stark' asset in 'designer' asset family is '"red"'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the 'stark' asset in 'designer' asset family is '"Value scopable_and_localizable_text-fr_FR-ecommerce"'
    And the value of the unlocalized unscoped multiple_colors of the 'stark' asset in 'designer' asset family is '["red","green"]'
    And the value of the unlocalized unscoped name of the 'ikea' asset in 'designer' asset family is '"Ikea"'
    And there is no value for the unlocalized ecommerce scopable_number of the 'ikea' asset in 'designer' asset family
    And the value of the en_US unscoped localizable_color of the 'ikea' asset in 'designer' asset family is '"blue"'
    And there is no value for the fr_FR ecommerce scopable_and_localizable_text of the 'ikea' asset in 'designer' asset family
    And the value of the unlocalized unscoped multiple_colors of the 'ikea' asset in 'designer' asset family is '["blue","green"]'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the '12345' asset in 'designer' asset family is '"12345"'

  @acceptance-back
  Scenario: Add warnings when import some records using a CSV file with wrong data
    When the user imports an invalid CSV file
    Then there is no exception thrown
    And 1 warning should be thrown
    And a warning should be thrown with 'This value is too long. It should have 20 characters or less' message
    And there is no 'bad_record' asset in the 'designer' asset family
    And there is a asset with:
      | code  | entity_identifier | labels                                           |
      | ikea  | designer          | {"en_US": "Ikea shop", "fr_FR": "Magasin Ikea"}  |

  @acceptance-back
  Scenario: Import some assets using a valid XLSX file
    When the user imports a valid XLSX file
    Then there is no exception thrown
    And there is no warning thrown
    And there is a asset with:
      | code  | entity_identifier | labels                                           |
      | stark | designer          | {"en_US": "New label", "fr_FR": "Nouveau label"} |
    And there is a asset with:
      | code  | entity_identifier | labels                                           |
      | ikea  | designer          | {"en_US": "Ikea shop", "fr_FR": "Magasin Ikea"}  |
    And there is a asset with:
      | code  | entity_identifier | labels                               |
      | 12345 | designer          | {"en_US": "12345", "fr_FR": "12345"} |
    And the value of the unlocalized unscoped name of the 'stark' asset in 'designer' asset family is '"Stark"'
    And the value of the unlocalized ecommerce scopable_number of the 'stark' asset in 'designer' asset family is '"10"'
    And the value of the en_US unscoped localizable_color of the 'stark' asset in 'designer' asset family is '"red"'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the 'stark' asset in 'designer' asset family is '"Value scopable_and_localizable_text-fr_FR-ecommerce"'
    And the value of the unlocalized unscoped multiple_colors of the 'stark' asset in 'designer' asset family is '["red","green"]'
    And the value of the unlocalized unscoped name of the 'ikea' asset in 'designer' asset family is '"Ikea"'
    And there is no value for the unlocalized ecommerce scopable_number of the 'ikea' asset in 'designer' asset family
    And the value of the en_US unscoped localizable_color of the 'ikea' asset in 'designer' asset family is '"blue"'
    And there is no value for the fr_FR ecommerce scopable_and_localizable_text of the 'ikea' asset in 'designer' asset family
    And the value of the unlocalized unscoped multiple_colors of the 'ikea' asset in 'designer' asset family is '["blue","green"]'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the '12345' asset in 'designer' asset family is '"12345"'

  @acceptance-back
  Scenario: Import some records using a valid archive file with csv and media
    When the user imports a valid archive file with csv and media
    Then there is no exception thrown
    And 1 warning should be thrown
    And a warning should be thrown with 'values.media: The file "in/memory/files/unexisting.jpg" was not found.' message
    And the value of the unlocalized unscoped media of the 'stark' asset in 'designer' asset family is '{"filePath":"in\/memory\/files\/saucisson.jpg","originalFilename":"saucisson.jpg","size":null,"mimeType":null,"extension":"jpg"}'
    And the value of the unlocalized unscoped picture of the 'stark' asset in 'designer' asset family is '{"filePath":"in\/memory\/files\/jambon.jpg","originalFilename":"jambon.jpg","size":null,"mimeType":null,"extension":"jpg"}'

  @acceptance-back
  Scenario: Import some records using a valid archive file with csv and media
    When the user imports a valid archive file with xlsx and media
    Then there is no exception thrown
    And 1 warning should be thrown
    And a warning should be thrown with 'values.media: The file "in/memory/files/unexisting.jpg" was not found.' message
    And the value of the unlocalized unscoped media of the 'stark' asset in 'designer' asset family is '{"filePath":"in\/memory\/files\/saucisson.jpg","originalFilename":"saucisson.jpg","size":null,"mimeType":null,"extension":"jpg"}'
    And the value of the unlocalized unscoped picture of the 'stark' asset in 'designer' asset family is '{"filePath":"in\/memory\/files\/jambon.jpg","originalFilename":"jambon.jpg","size":null,"mimeType":null,"extension":"jpg"}'
