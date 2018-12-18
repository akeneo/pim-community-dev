Feature: Create an attribute linked to a reference entity
  In order to create an attribute linked to a reference entity
  As a user
  I want create an attribute linked to a reference entity

  Background:
    Given a valid reference entity

  @acceptance-back
  Scenario: Create an image attribute linked to a reference entity
    When the user creates an image attribute "image" linked to the reference entity "designer" with:
      | code  | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions |
      | image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     |
    Then there is an image attribute "image" in the reference entity "designer" with:
      | code  | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type  |
      | image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     | image |

  @acceptance-back
  Scenario: Create a text attribute linked to a reference entity
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there is a text attribute "name" in the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length | type | is_textarea | is_rich_text_editor | validation_rule | regular_expression |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         | text | false       | false               | none            |                    |

  @acceptance-back
  Scenario: Create a record attribute linked to a reference entity
    When the user creates a record attribute "mentor" linked to the reference entity "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | record_type |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 0     | false             | false            | designer    |
    Then there is a record attribute "mentor" in the reference entity "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | record_type | type   |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 0     | false             | false            | designer    | record |

  @acceptance-back
  Scenario: Create a record collection attribute linked to a reference entity
    When the user creates a record collection attribute "brands" linked to the reference entity "designer" with:
      | code   | labels                                  | is_required | order | value_per_channel | value_per_locale | record_type |
      | brands | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 0     | false             | false            | brand       |
    Then there is a record attribute "brands" in the reference entity "designer" with:
      | code   | labels                                  | is_required | order | value_per_channel | value_per_locale | record_type | type              |
      | brands | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 0     | false             | false            | brand       | record_collection |

  @acceptance-back
  Scenario: Cannot create an attribute for a reference entity if it already exists
    Given the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then an exception is thrown

  @acceptance-back
  Scenario: Cannot create an attribute with the same order for a reference entity
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    And the user creates a text attribute "bio" linked to the reference entity "designer" with:
      | code | labels                                  | is_required | order | value_per_channel | value_per_locale | max_length |
      | bio  | {"en_US": "Bio", "fr_FR": "Biographie"} | true        | 0     | true              | false            | 44         |
    Then an exception is thrown

  @acceptance-back
  Scenario Outline: Cannot create an attribute with a reserverd word as code
    When the user creates a text attribute "code" linked to the reference entity "designer" with:
      | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there should be a validation error on the property 'code' with message '<message>'
    And there is no exception thrown

    Examples:
      | invalid_attribute_code | message                                                            |
      | label                  | The code cannot be any of those values: "{{ code, label, image }}" |
      | code                   | The code cannot be any of those values: "{{ code, label, image }}" |

  @acceptance-front
  Scenario: Create a simple valid text attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates a valid attribute
    And the user saves the valid attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create an invalid text attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates an attribute with an invalid code
    And the user saves the attribute with an invalid code
    Then the user should see the validation error "This field may only contain letters, numbers and underscores."

  @acceptance-front
  Scenario: Create a simple valid record attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates a valid record attribute
    And the user saves the valid record attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid record collection attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates a valid record collection attribute
    And the user saves the valid record collection attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid image attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates a valid image attribute
    And the user saves the valid image attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid option attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates a valid option attribute
    And the user saves the valid option attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid option collection attribute
    Given the user has the following rights:
      | akeneo_referenceentity_attribute_create | true |
    When the user creates a valid option collection attribute
    And the user saves the valid option collection attribute
    Then the user should not see any validation error

  @acceptance-back
  Scenario: Cannot create more text attributes than the limit
    Given 100 random attributes for a reference entity
    When the user creates a text attribute "name" linked to the reference entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there should be a validation error with message 'You cannot create the attribute "Stylist" because you have reached the limit of 100 attributes for this reference entity'

  @acceptance-back
  Scenario: Cannot create more image attributes than the limit
    Given 100 random attributes for a reference entity
    When the user creates an image attribute "stylist" linked to the reference entity "designer" with:
      | labels                                         | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions |
      | {"en_US": "Stylist view", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     |
    Then there should be a validation error with message 'You cannot create the attribute "Stylist view" because you have reached the limit of 100 attributes for this reference entity'

  @acceptance-back
  Scenario: Cannot create more record attributes than the limit
    Given 100 random attributes for a reference entity
    When the user creates a record attribute "mentor" linked to the reference entity "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | record_type |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 0     | false             | false            | designer    |
    Then there should be a validation error with message 'You cannot create the attribute "Mentor" because you have reached the limit of 100 attributes for this reference entity'

  @acceptance-back
  Scenario: Cannot create more record collection attributes than the limit
    Given 100 random attributes for a reference entity
    When the user creates a record collection attribute "brands" linked to the reference entity "designer" with:
      | code   | labels                                  | is_required | order | value_per_channel | value_per_locale | record_type |
      | brands | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 0     | false             | false            | brand       |
    Then there should be a validation error with message 'You cannot create the attribute "Brands" because you have reached the limit of 100 attributes for this reference entity'

  @acceptance-back
  Scenario: Create an option attribute
    When the user creates an option attribute "color" with:
      | labels             | is_required | order | value_per_channel | value_per_locale |
      | {"en_US": "Color"} | false       | 0     | false             | false            |
    Then there is an option attribute "color" with:
      | labels             | is_required | order | value_per_channel | value_per_locale | type   |
      | {"en_US": "Color"} | false       | 0     | false             | false            | option |

  @acceptance-back
  Scenario: Create an option collection attribute
    When the user creates an option collection attribute "favorite_colors" with:
      | labels                       | is_required | order | value_per_channel | value_per_locale |
      | {"en_US": "Favorite colors"} | true        | 0     | false             | false            |
    Then there is an option collection attribute "favorite_colors" with:
      | labels                       | is_required | order | value_per_channel | value_per_locale | type              |
      | {"en_US": "Favorite colors"} | true        | 0     | false             | false            | option_collection |

  @acceptance-back
  Scenario: Cannot create more option attributes than the limit
    Given 100 random attributes for a reference entity
    When the user creates an option attribute "favorite_color"
      | labels                                 | is_required | order | value_per_channel | value_per_locale | record_type |
      | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 0     | false             | false            | designer    |
    Then there should be a validation error with message 'You cannot create the attribute "Mentor" because you have reached the limit of 100 attributes for this reference entity'

  @acceptance-back
  Scenario: Cannot create more option collection attributes than the limit
    Given 100 random attributes for a reference entity
    When the user creates an option collection attribute "favorite_colors"
      | labels                                  | is_required | order | value_per_channel | value_per_locale | record_type |
      | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 0     | false             | false            | brand       |
    Then there should be a validation error with message 'You cannot create the attribute "Brands" because you have reached the limit of 100 attributes for this reference entity'
