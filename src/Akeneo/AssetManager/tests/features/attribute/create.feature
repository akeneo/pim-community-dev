Feature: Create an attribute linked to an asset family
  In order to create an attribute linked to an asset family
  As a user
  I want create an attribute linked to an asset family

  Background:
    Given a valid asset family

  @acceptance-back
  Scenario: Create an image attribute linked to an asset family
    When the user creates an image attribute "another_image" linked to the asset family "designer" with:
      | code          | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions |
      | another_image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 250.0         | ["png", "jpg"]     |
    Then there is an image attribute "another_image" in the asset family "designer" with:
      | code          | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type  |
      | another_image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 250.0         | ["png", "jpg"]     | image |

  @acceptance-back
  Scenario: Create a text attribute linked to an asset family
    When the user creates a text attribute "name" linked to the asset family "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 44         |
    Then there is a text attribute "name" in the asset family "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length | type | is_textarea | is_rich_text_editor | validation_rule | regular_expression |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 44         | text | false       | false               | none            |                    |

  @acceptance-back
  Scenario: Create a asset attribute linked to an asset family
    When the user creates a asset attribute "mentor" linked to the asset family "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | asset_type |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 2     | false             | false            | designer    |
    Then there is a asset attribute "mentor" in the asset family "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | asset_type | type   |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 2     | false             | false            | designer    | asset |

  @acceptance-back
  Scenario: Create a asset collection attribute linked to an asset family
    When the user creates a asset collection attribute "brands" linked to the asset family "designer" with:
      | code   | labels                                  | is_required | order | value_per_channel | value_per_locale | asset_type |
      | brands | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 2     | false             | false            | brand       |
    Then there is a asset attribute "brands" in the asset family "designer" with:
      | code   | labels                                  | is_required | order | value_per_channel | value_per_locale | asset_type | type              |
      | brands | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 2     | false             | false            | brand       | asset_collection |

  @acceptance-back
  Scenario: Cannot create an attribute for an asset family if it already exists
    Given the user creates a text attribute "name" linked to the asset family "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 44         |
    When the user creates a text attribute "name" linked to the asset family "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 44         |
    Then an exception is thrown

  @acceptance-back
  Scenario Outline: Cannot create an attribute with a reserverd word as code
    When the user creates a text attribute "code" linked to the asset family "designer" with:
      | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 2     | true              | false            | 44         |
    Then there should be a validation error on the property 'code' with message '<message>'
    And there is no exception thrown

    Examples:
      | invalid_attribute_code | message                                                            |
      | label                  | The code cannot be any of those values: "{{ code, label, image }}" |
      | code                   | The code cannot be any of those values: "{{ code, label, image }}" |

  @acceptance-back
  Scenario: Cannot create a asset attribute with a asset type that refers to an asset family that does not exist
    When the user creates a asset attribute "mentor" linked to the asset family "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | asset_type |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 2     | false             | false            | foo         |
    Then there should be a validation error on the property 'asset_family_code' with message 'The asset family "foo" was not found.'
    And there is no exception thrown

  @acceptance-back
  Scenario: Cannot create more text attributes than the limit
    Given 100 random attributes for an asset family
    When the user creates a text attribute "name" linked to the asset family "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there should be a validation error with message 'You cannot create the attribute "Stylist" because you have reached the limit of 100 attributes for this asset family'

  @acceptance-back
  Scenario: Cannot create more image attributes than the limit
    Given 100 random attributes for an asset family
    When the user creates an image attribute "stylist" linked to the asset family "designer" with:
      | labels                                         | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions |
      | {"en_US": "Stylist view", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     |
    Then there should be a validation error with message 'You cannot create the attribute "Stylist view" because you have reached the limit of 100 attributes for this asset family'

  @acceptance-back
  Scenario: Cannot create more asset attributes than the limit
    Given 100 random attributes for an asset family
    When the user creates a asset attribute "mentor" linked to the asset family "designer" with:
      | code   | labels                                 | is_required | order | value_per_channel | value_per_locale | asset_type |
      | mentor | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 0     | false             | false            | designer    |
    Then there should be a validation error with message 'You cannot create the attribute "Mentor" because you have reached the limit of 100 attributes for this asset family'

  @acceptance-back
  Scenario: Cannot create more asset collection attributes than the limit
    Given 100 random attributes for an asset family
    When the user creates a asset collection attribute "brands" linked to the asset family "designer" with:
      | code   | labels                                  | is_required | order | value_per_channel | value_per_locale | asset_type |
      | brands | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 0     | false             | false            | brand       |
    Then there should be a validation error with message 'You cannot create the attribute "Brands" because you have reached the limit of 100 attributes for this asset family'

  @acceptance-back
  Scenario: Create an option attribute
    When the user creates an option attribute "color" with:
      | labels             | is_required | order | value_per_channel | value_per_locale |
      | {"en_US": "Color"} | false       | 2     | false             | false            |
    Then there is an option attribute "color" with:
      | labels             | is_required | order | value_per_channel | value_per_locale | type   |
      | {"en_US": "Color"} | false       | 2     | false             | false            | option |

  @acceptance-back
  Scenario: Create an option collection attribute
    When the user creates an option collection attribute "favorite_colors" with:
      | labels                       | is_required | order | value_per_channel | value_per_locale |
      | {"en_US": "Favorite colors"} | true        | 2     | false             | false            |
    Then there is an option collection attribute "favorite_colors" with:
      | labels                       | is_required | order | value_per_channel | value_per_locale | type              |
      | {"en_US": "Favorite colors"} | true        | 2     | false             | false            | option_collection |

  @acceptance-back
  Scenario: Cannot create more option attributes than the limit
    Given 100 random attributes for an asset family
    When the user creates an option attribute "favorite_color"
      | labels                                 | is_required | order | value_per_channel | value_per_locale | asset_type |
      | {"en_US": "Mentor", "fr_FR": "Mentor"} | false       | 0     | false             | false            | designer    |
    Then there should be a validation error with message 'You cannot create the attribute "Mentor" because you have reached the limit of 100 attributes for this asset family'

  @acceptance-back
  Scenario: Cannot create more option collection attributes than the limit
    Given 100 random attributes for an asset family
    When the user creates an option collection attribute "favorite_colors"
      | labels                                  | is_required | order | value_per_channel | value_per_locale | asset_type |
      | {"en_US": "Brands", "fr_FR": "Marques"} | true        | 0     | false             | false            | brand       |
    Then there should be a validation error with message 'You cannot create the attribute "Brands" because you have reached the limit of 100 attributes for this asset family'

  @acceptance-back
  Scenario: Create a non decimal number attribute to an asset family
    When the user creates a number attribute "area" to the asset family "city" with:
      | code | labels                                   | is_required | order | value_per_channel | value_per_locale | decimals_allowed | min_value | max_value |
      | area | {"en_US": "Area", "fr_FR": "Superficie"} | true        | 0     | false             | false            | false          | 10        | 11        |
    Then there is a number attribute "area" in the asset family "city" with:
      | code | labels                                   | is_required | order | value_per_channel | value_per_locale | type   | decimals_allowed | min_value | max_value |
      | area | {"en_US": "Area", "fr_FR": "Superficie"} | true        | 0     | false             | false            | number | false          | 10        | 11        |

  @acceptance-back
  Scenario: Create an invalid number attribute on an asset family
    When the user creates a number attribute "area" to the asset family "city" with:
      | code | labels                                   | is_required | order | value_per_channel | value_per_locale | decimals_allowed | min_value | max_value |
      | area | {"en_US": "Area", "fr_FR": "Superficie"} | true        | 0     | false             | false            | false          | 10        | hello     |
    Then there should be a validation error with message 'This value should be a number with the right decimal separator.'

  @acceptance-back
  Scenario: Create an url attribute to an asset family
    When the user creates an url attribute "dam_image" to the asset family "city" with:
      | code  | labels                                   | is_required | order | value_per_channel | value_per_locale | media_type | prefix | suffix |
      | image | {"en_US": "Image", "fr_FR": "Image"}     | true        | 0     | false             | false            | image        | null   | null   |
    Then there is an url attribute "dam_image" in the asset family "city" with:
      | code  | labels                                   | is_required | order | value_per_channel | value_per_locale | type | media_type | prefix | suffix |
      | image | {"en_US": "Image", "fr_FR": "Image"}     | true        | 0     | false             | false            | url  | image        | null   | null   |

  @acceptance-back
  Scenario: Create an url attribute to an asset family
    When the user creates an url attribute "dam_image" to the asset family "city" with:
      | code  | labels                               | is_required | order | value_per_channel | value_per_locale | media_type | prefix                | suffix   |
      | image | {"en_US": "Image", "fr_FR": "Image"} | true        | 0     | false             | false            | image        | http://my-prefix.com/ | /500x500 |
    Then there is an url attribute "dam_image" in the asset family "city" with:
      | code  | labels                                   | is_required | order | value_per_channel | value_per_locale | type | media_type | prefix                | suffix   |
      | image | {"en_US": "Image", "fr_FR": "Image"}     | true        | 0     | false             | false            | url  | image        | http://my-prefix.com/ | /500x500 |

  @acceptance-back
  Scenario: Create an invalid url attribute on an asset family
    When the user creates an url attribute "dam_image" to the asset family "city" with:
      | code  | labels                                   | is_required | order | value_per_channel | value_per_locale | media_type | prefix | suffix |
      | image | {"en_US": "Image", "fr_FR": "Image"}     | true        | 0     | false             | false            | video        | null   | null   |
    Then there should be a validation error with message 'The media type given is not corresponding to the expected ones (image, other).'

  @acceptance-front
  Scenario: Create a simple valid text attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid attribute
    And the user saves the valid attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create an invalid text attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates an attribute with an invalid code
    And the user saves the attribute with an invalid code
    Then the user should see the validation error "This field may only contain letters, numbers and underscores."

  @acceptance-front
  Scenario: Create a simple valid asset attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid asset attribute
    And the user saves the valid asset attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid asset collection attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid asset collection attribute
    And the user saves the valid asset collection attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid image attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid image attribute
    And the user saves the valid image attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid option attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid option attribute
    And the user saves the valid option attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a simple valid option collection attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid option collection attribute
    And the user saves the valid option collection attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: Create a number attribute
    Given the user has the following rights:
      | akeneo_assetmanager_attribute_create | true |
    When the user creates a valid number attribute
    And the user saves the valid number attribute
    Then the user should not see any validation error

  @acceptance-front
  Scenario: User can't create an attribute without the good rights
    Given the user does not have any rights
    Then the user should not see the add attribute button
