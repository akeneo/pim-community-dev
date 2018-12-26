@acceptance-back
Feature: Retrieve some family attributes from Franklin

  Background:
    Given Franklin is configured with a valid token

  Scenario: Successfully retrieve the attributes mapping
    Given the family "router"
    When I retrieve the attributes mapping for the family "router"
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | target_attribute_type | pim_attribute_code | status  |
      | product_weight        | Product Weight         | metric                |                    | pending |
      | color                 | Color                  | multiselect           | product color      | active  |

  Scenario: Successfully retrieve the attributes mapping with unknown attribute type
    Given the family "webcam"
    When I retrieve the attributes mapping for the family "webcam"
    Then the retrieved attributes mapping for the family "webcam" should be:
      | target_attribute_code  | target_attribute_label        | target_attribute_type | pim_attribute_code | status  |
      | idontreallyknowwhatiam | An attribute that has no type | unknown               |                    | pending |

  Scenario: Successfully retrieve an empty attributes mapping
    Given the family "camcorders"
    When I retrieve the attributes mapping for the family "camcorders"
    Then the retrieved attributes mapping should be empty

  Scenario: Fail to retrieve the attributes mapping from an unexisting family

  Scenario: Fail to retrieve the attributes mapping when the token is invalid

  Scenario: Fail to retrieve the attributes mapping when Franklin is down

