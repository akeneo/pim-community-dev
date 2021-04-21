Feature: Import records via files
  In order to create and update some records
  As a user
  I want to import some records via files

  Background:
    Given a valid reference entity
    And the "city" reference entity
    And the 'ecommerce,mobile' channels with 'en_US,fr_FR' locales
    And a "name" text attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale | max_length |
      | false             | false            | 200        |
    And a "scopable_number" number attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | true              | false            |
    And a "scopable_text" text attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | true              | false            |
    And a "localizable_text" text attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | false             | true             |
    And a "localizable_color" option attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | false             | true             |
    And the "localizable_color" option attribute contains "red,blue,green" options
    And a "scopable_and_localizable_text" text attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | true              | true             |
    And a "multiple_colors" option_collection attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | false             | false            |
    And a "name_with_validation" text attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale | max_length |
      | false             | false            | 20         |
    And a "record_link" record attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale | record_type |
      | false             | false            | city        |
    And a "record_links" record_collection attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale | record_type |
      | false             | false            | city        |
    And a "picture" image attribute linked to the "designer" reference entity with:
      | value_per_channel | value_per_locale |
      | false             | false            |
    And the "multiple_colors" options attribute contains "red,blue,green" options
    And a record belonging to this reference entity with a value of "Philippe stark" for the text attribute
    And the "nantes" record for "city" entity with:
      | labels |
      | []     |
    And the "paris" record for "city" entity with:
      | labels |
      | []     |

  @acceptance-back
  Scenario: Import some records using a valid CSV file
    When the user imports a valid CSV file
    Then there is no exception thrown
    And there is no warning thrown
    And there is a record with:
      | code  | entity_identifier | labels                                           |
      | stark | designer          | {"en_US": "New label", "fr_FR": "Nouveau label"} |
    And there is a record with:
      | code  | entity_identifier | labels                                           |
      | ikea  | designer          | {"en_US": "Ikea shop", "fr_FR": "Magasin Ikea"}  |
    And there is a record with:
      | code  | entity_identifier | labels                               |
      | 12345 | designer          | {"en_US": "12345", "fr_FR": "12345"} |
    And the value of the unlocalized unscoped name of the 'stark' record in 'designer' reference entity is '"Stark"'
    And the value of the unlocalized ecommerce scopable_number of the 'stark' record in 'designer' reference entity is '"10"'
    And the value of the en_US unscoped localizable_color of the 'stark' record in 'designer' reference entity is '"red"'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the 'stark' record in 'designer' reference entity is '"Value scopable_and_localizable_text-fr_FR-ecommerce"'
    And the value of the unlocalized unscoped multiple_colors of the 'stark' record in 'designer' reference entity is '["red","green"]'
    And the value of the unlocalized unscoped name of the 'ikea' record in 'designer' reference entity is '"Ikea"'
    And there is no value for the unlocalized ecommerce scopable_number of the 'ikea' record in 'designer' reference entity
    And the value of the en_US unscoped localizable_color of the 'ikea' record in 'designer' reference entity is '"blue"'
    And there is no value for the fr_FR ecommerce scopable_and_localizable_text of the 'ikea' record in 'designer' reference entity
    And the value of the unlocalized unscoped multiple_colors of the 'ikea' record in 'designer' reference entity is '["blue","green"]'
    And the value of the unlocalized unscoped record_link of the 'ikea' record in 'designer' reference entity is '"nantes"'
    And the value of the unlocalized unscoped record_links of the 'ikea' record in 'designer' reference entity is '["paris","nantes"]'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the '12345' record in 'designer' reference entity is '"12345"'

  @acceptance-back
  Scenario: Add warnings when import some records using a CSV file with wrong data
    When the user imports an invalid CSV file
    Then there is no exception thrown
    And 1 warning should be thrown
    And a warning should be thrown with 'This value is too long. It should have 20 characters or less' message
    And there is no 'bad_record' record in the 'designer' reference entity
    And there is a record with:
      | code  | entity_identifier | labels                                           |
      | ikea  | designer          | {"en_US": "Ikea shop", "fr_FR": "Magasin Ikea"}  |

  @acceptance-back
  Scenario: Import some records using a valid XLSX file
    When the user imports a valid XLSX file
    Then there is no exception thrown
    And there is no warning thrown
    And there is a record with:
      | code  | entity_identifier | labels                                           |
      | stark | designer          | {"en_US": "New label", "fr_FR": "Nouveau label"} |
    And there is a record with:
      | code  | entity_identifier | labels                                           |
      | ikea  | designer          | {"en_US": "Ikea shop", "fr_FR": "Magasin Ikea"}  |
    And there is a record with:
      | code  | entity_identifier | labels                               |
      | 12345 | designer          | {"en_US": "12345", "fr_FR": "12345"} |
    And the value of the unlocalized unscoped name of the 'stark' record in 'designer' reference entity is '"Stark"'
    And the value of the unlocalized ecommerce scopable_text of the 'stark' record in 'designer' reference entity is '"Value scopable_text-ecommerce"'
    And the value of the en_US unscoped localizable_text of the 'stark' record in 'designer' reference entity is '"Value localizable_text-en_US"'
    And the value of the fr_FR ecommerce scopable_and_localizable_text of the 'stark' record in 'designer' reference entity is '"Value scopable_and_localizable_text-fr_FR-ecommerce"'
    And the value of the unlocalized unscoped name of the 'ikea' record in 'designer' reference entity is '"Ikea"'
    And there is no value for the unlocalized ecommerce scopable_text of the 'ikea' record in 'designer' reference entity
    And the value of the en_US unscoped localizable_text of the 'stark' record in 'designer' reference entity is '"Value localizable_text-en_US"'
    And there is no value for the fr_FR ecommerce scopable_and_localizable_text of the 'ikea' record in 'designer' reference entity
    And the value of the en_US unscoped localizable_text of the '12345' record in 'designer' reference entity is '"12345"'

  @acceptance-back
  Scenario: Import some records using a valid archive file with csv and media
    When the user imports a valid archive file with csv and media
    Then there is no exception thrown
    And 1 warning should be thrown
    And a warning should be thrown with 'values.picture: The file "in/memory/files/unknown.jpg" was not found.' message
    And there is no value for the unlocalized unscoped name of the 'stark' record in 'designer' reference entity
    And there is no value for the unlocalized unscoped picture of the 'stark' record in 'designer' reference entity
    And the value of the unlocalized unscoped name of the 'ikea' record in 'designer' reference entity is '"Ikea"'
    And the value of the unlocalized unscoped picture of the 'ikea' record in 'designer' reference entity is '{"filePath":"in\/memory\/files\/dog.jpg","originalFilename":"dog.jpg","size":null,"mimeType":null,"extension":"jpg"}'

  @acceptance-back
  Scenario: Import some records using a valid archive file with xlsx and media
    When the user imports a valid archive file with xlsx and media
    Then there is no exception thrown
    And 1 warning should be thrown
    And a warning should be thrown with 'values.picture: The file "in/memory/files/unknown.jpg" was not found.' message
    And there is no value for the unlocalized unscoped name of the 'stark' record in 'designer' reference entity
    And there is no value for the unlocalized unscoped picture of the 'stark' record in 'designer' reference entity
    And the value of the unlocalized unscoped name of the 'ikea' record in 'designer' reference entity is '"New name"'
    And the value of the unlocalized unscoped picture of the 'ikea' record in 'designer' reference entity is '{"filePath":"in\/memory\/files\/dog.jpg","originalFilename":"dog.jpg","size":null,"mimeType":null,"extension":"jpg"}'
