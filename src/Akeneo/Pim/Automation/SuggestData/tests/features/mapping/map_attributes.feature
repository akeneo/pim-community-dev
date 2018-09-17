@acceptance-back
Feature:

  Scenario: Successfully retrieve the attributes mapping
    Given the family "router"
    When PIM.ai is configured with a valid token
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | pim_attribute_code | status  |
      | product_weight        | Product Weight         |                    | pending |
      | color                 | Color                  | product color      | active  |
