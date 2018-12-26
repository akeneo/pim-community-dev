@acceptance-back
Feature: Retrieve some family attributes from Franklin

  Background:
    Given Franklin is configured with a valid token

  Scenario: Successfully retrieve an attributes mapping
    Given the family "router"
    When I retrieve the attributes mapping for the family "router"
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | target_attribute_type | pim_attribute_code | status  |
      | product_weight        | Product Weight         | metric                |                    | pending |
      | color                 | Color                  | multiselect           | product color      | active  |

  Scenario: Successfully retrieve an attributes mapping with unknown attribute type
    Given the family "webcam"
    When I retrieve the attributes mapping for the family "webcam"
    Then the retrieved attributes mapping for the family "webcam" should be:
      | target_attribute_code  | target_attribute_label        | target_attribute_type | pim_attribute_code | status  |
      | idontreallyknowwhatiam | An attribute that has no type | unknown               |                    | pending |

  Scenario: Successfully retrieve an empty attributes mapping
    Given the family "camcorders"
    When I retrieve the attributes mapping for the family "camcorders"
    Then the retrieved attributes mapping should be empty

  Scenario: Fail to retrieve an attributes mapping from an unexisting family
    When I retrieve the attributes mapping for the family "unexisting"
    Then an unknown family message should be sent

  Scenario: Fail to retrieve an attributes mapping when the token is invalid
    Given the family "webcam"
    And Franklin is configured with an expired token
    When I retrieve the attributes mapping for the family "webcam"
    Then an authentication error message should be sent

  Scenario: Fail to retrieve an attributes mapping when Franklin is down
    Given the family "webcam"
    And Franklin server is down
    When I retrieve the attributes mapping for the family "webcam"
    And a data provider error message should be sent


