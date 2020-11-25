Feature: Validate asset multiple link attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for reference entity attributes

  Background:
    Given an authentified user
    And the "designers" reference entity with:
      | labels               |
      | {"en_US": "Stylist"} |
    And the "starck" record for "designers" entity with:
      | labels                       |
      | {"en_US": "Philippe Starck"} |
    And the "dyson" record for "designers" entity with:
      | labels                    |
      | {"en_US": "James Dyson"} |
    And the "colors" reference entity with:
      | labels             |
      | {"en_US": "Color"} |
    And the "red" record for "colors" entity with:
      | labels           |
      | {"en_US": "Red"} |
    And the "blue" record for "colors" entity with:
      | labels            |
      | {"en_US": "Blue"} |
    And the following attributes:
      | code        | type                               | reference_data_name |
      | sku         | pim_catalog_identifier             |                     |
      | designer    | akeneo_reference_entity            | designers           |
      | main_colors | akeneo_reference_entity_collection | colors              |

  @acceptance-back
  Scenario: Providing an existing record code for a reference entity single link should not raise an error
    When a product is created with values:
      | attribute | data    | scope | locale |
      | designer  | starck  |       |        |
    Then no error is raised

  @acceptance-back
  Scenario: Providing a non-existing record code for a reference entity single link should raise an error
    When a product is created with values:
      | attribute | data  | scope | locale |
      | designer  | arad  |       |        |
    Then the error 'Property "designer" expects a valid record code. The record "arad" does not exist' is raised

  @acceptance-back
  Scenario: Providing existing record codes for a reference entity multiple link should not raise an error
    When a product is created with values:
      | attribute   | data     | scope | locale |
      | main_colors | blue,red |       |        |
    Then no error is raised

  @acceptance-back
  Scenario: Providing non existing record codes for a reference entity multiple link should raise an error
    When a product is created with values:
      | attribute   | data                  | scope | locale |
      | main_colors | red,green,blue,yellow |       |        |
    Then the error 'Property "main_colors" expects valid record codes. The following records do not exist: "green, yellow"' is raised
