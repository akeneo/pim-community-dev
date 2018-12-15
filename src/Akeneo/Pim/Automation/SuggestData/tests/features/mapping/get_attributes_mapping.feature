@acceptance-back
Feature: Retrieve some family attributes from Franklin

  Scenario: Successfully retrieve the attributes mapping
    Given the family "router"
    And Franklin is configured with a valid token
    When I retrieves the attributes mapping for the family "router"
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | target_attribute_type | pim_attribute_code | status  |
      | product_weight        | Product Weight         | metric                |                    | pending |
      | color                 | Color                  | multiselect           | product color      | active  |

  Scenario: Successfully retrieve the attributes mapping with unknown attribute type
    Given the family "webcam"
    And Franklin is configured with a valid token
    When I retrieves the attributes mapping for the family "webcam"
    Then the retrieved attributes mapping for the family "webcam" should be:
      | target_attribute_code  | target_attribute_label        | target_attribute_type | pim_attribute_code | status  |
      | idontreallyknowwhatiam | An attribute that has no type | unknown               |                    | pending |

  #TODO: Adds more scenarios in APAI-456
