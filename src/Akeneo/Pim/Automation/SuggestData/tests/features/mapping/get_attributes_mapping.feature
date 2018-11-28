@acceptance-back
Feature: Retrieve some family attributes from Franklin

  Scenario: Successfully retrieve the attributes mapping with unknown type
    Given the family "webcam"
    And Franklin is configured with a valid token
    When Julia retrieves the attributes mapping for the family "router"
    Then the retrieved attributes mapping for the family "router" should be:
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | pim_attribute_code | status  |
      | product_weight        | Product Weight         |                    | pending |
      | color                 | Color                  | product color      | active  |
