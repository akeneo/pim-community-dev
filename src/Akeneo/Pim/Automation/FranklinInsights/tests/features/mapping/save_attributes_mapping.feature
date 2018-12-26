@acceptance-back
Feature: Map the PIM attributes with Franklin attributes
  In order to automatically enrich my products
  As a system administrator
  I want to map Franklin attributes to Akeneo PIM attributes

  @critical
  Scenario: Successfully save the attributes mapping for the first time
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        |                    | pending |
      | color                 | color              | active  |
    Then the attributes mapping should be saved as follows:
      | target_attribute_code | pim_attribute_code | pim_attribute_type | status   |
      | product_weight        |                    |                    | inactive |
      | color                 | color              | select             | active   |

    #Then Franklin attribute "product_weight" should not be mapped (inactive)
    #And Franklin attribute "color" should be mapped to PIM "product_color" (activated)

  Scenario: Successfully udpdate the attributes mapping
    Given the family "router"
    And Franklin is configured with a valid token
    And a predefined attributes mapping for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        |                    | pending |
      | color                 | color              | active  |
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | weight             | active  |
      | color                 | color              | active  |
    Then the attributes mapping should be saved as follows:
      | target_attribute_code | pim_attribute_code | pim_attribute_type | status   |
      | product_weight        | weight             | number             | active   |
      | color                 | color              | select             | active   |

  Scenario: Fails to save an empty attribute mapping
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes mapping for the family "router" is updated with an empty mapping
    Then the attributes mapping should not be saved

  Scenario: Fails to save the attributes mapping if the family does not exist
    Given Franklin is configured with a valid token
    When the attributes are mapped for the family "unknown" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | weight             | active  |
    Then the attributes mapping should not be saved

  Scenario: Fails to save the attributes mapping if one on the mapped attribute does not exist
    Given the family "router"
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | color                 | unknown_attribute  | active   |
    Then the attributes mapping should not be saved

  Scenario Outline: Fails to save the attributes mapping if an attribute type is invalid
    Given the family "router"
    And the following attribute:
      | code              | type             |
      | invalid_type_attr | <attribute_type> |
    And Franklin is configured with a valid token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | color                 | invalid_type_attr  | active  |
    Then the attributes mapping should not be saved

  Examples:
    | attribute_type                  |
    | pim_catalog_identifier          |
    | pim_catalog_date                |
    | pim_catalog_file                |
    | pim_catalog_image               |
    | pim_catalog_price_collection    |
    | pim_reference_data_multiselect  |
    | pim_reference_data_simpleselect |
    | akeneo_reference_entity         |

  Scenario: Fails to save for the first time the attributes mapping when Franklin is down
    Given the family "router"
    And Franklin is configured with a valid token
    And a predefined attributes mapping for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        |                    | pending |
      | color                 | color              | active  |
    And Franklin server is down
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | weight             | active  |
      | color                 | color              | active  |
    Then the attributes mapping should not be saved

  Scenario: Fails to udate the attributes mapping when Franklin is down
    Given the family "router"
    And Franklin is configured with a valid token
    And Franklin server is down
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | weight             | active  |
      | color                 | color              | active  |
    Then the attributes mapping should not be saved

  Scenario: Fails to udate the attributes mapping when the token is expired
    Given the family "router"
    And Franklin is configured with an expired token
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | weight             | active  |
      | color                 | color              | active  |
    Then the attributes mapping should not be saved
    And an authentication error message should be sent

  Scenario: Fails to map Franklin attributes with localizable PIM attributes
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute:
      | code              | type             | localizable |
      | localizable_attr  | pim_catalog_text | true        |
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | localizable_attr   | active  |
    Then the attributes mapping should not be saved

  Scenario: Fails to map Franklin attributes with scopable PIM attributes
    Given the family "router"
    And Franklin is configured with a valid token
    And the following attribute:
      | code              | type             | scopable |
      | scopable_attr     | pim_catalog_text | true     |
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | scopable_attr      | active  |
    Then the attributes mapping should not be saved

  Scenario: Fails to map Franklin attributes with locale specific PIM attributes
    Given the family "router"
    And Franklin is configured with a valid token
    And the following locales "en_US"
    And the following text attribute "pim_weight" specific to locale en_US
    When the attributes are mapped for the family "router" as follows:
      | target_attribute_code | pim_attribute_code | status  |
      | product_weight        | pim_weight         | active  |
    Then the attributes mapping should not be saved
