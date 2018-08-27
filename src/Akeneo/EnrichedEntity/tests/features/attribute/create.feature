Feature: Create an attribute linked to an enriched entity
  In order to create an attribute linked to an enriched entity
  As a user
  I want create an attribute linked to an enriched entity

  Background:
    Given the following enriched entity:
      | identifier | labels                                       |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} |

  @acceptance-back
  Scenario: Creating an image attribute linked to an enriched entity
    When the user creates an image attribute "image" linked to the enriched entity "designer" with:
      | code  | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions |
      | image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     |
    Then there is an image attribute "image" in the enriched entity "designer" with:
      | code  | labels                                    | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type  |
      | image | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 250.0         | ["png", "jpg"]     | image |

  @acceptance-back
  Scenario: Creating a text attribute linked to an enriched entity
    When the user creates a text attribute "name" linked to the enriched entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then there is a text attribute "name" in the enriched entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length | type | is_text_area | is_rich_text_editor | validation_rule | regular_expression |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         | text | 0            | 0                   |                 |                    |

  @acceptance-back
  Scenario: Cannot create an attribute for an enriched entity if it already exists
    Given the user creates a text attribute "name" linked to the enriched entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    When the user creates a text attribute "name" linked to the enriched entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    Then an exception is thrown

  @acceptance-back
  Scenario: Cannot create an attribute with the same order for an enriched entity
    When the user creates a text attribute "name" linked to the enriched entity "designer" with:
      | code | labels                                    | is_required | order | value_per_channel | value_per_locale | max_length |
      | name | {"en_US": "Stylist", "fr_FR": "Styliste"} | true        | 0     | true              | false            | 44         |
    And the user creates a text attribute "bio" linked to the enriched entity "designer" with:
      | code | labels                                  | is_required | order | value_per_channel | value_per_locale | max_length |
      | bio  | {"en_US": "Bio", "fr_FR": "Biographie"} | true        | 0     | true              | false            | 44         |
    Then an exception is thrown

