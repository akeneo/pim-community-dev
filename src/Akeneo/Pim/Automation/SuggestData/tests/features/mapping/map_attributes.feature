@acceptance-back
Feature: Map some family attributes with PIM.ai attributes

  Scenario: Successfully retrieve the attributes mapping
    Given the family "router"
    When PIM.ai is configured with a valid token
    Then the retrieved attributes mapping for the family "router" should be:
      | target_attribute_code | target_attribute_label | pim_attribute_code | status  |
      | product_weight        | Product Weight         |                    | pending |
      | color                 | Color                  | product color      | active  |

  Scenario: search for all the families
    Given the family "router"
    And the family "camcorders"
    And the family "webcam"
    When I search for all the families
    Then I should have the families router, camcorders and webcam

  Scenario: search families
    Given the family "router"
    And the family "camcorders"
    And the family "webcam"
    When I search a family with the query "cam"
    Then I should have the family camcorders and webcam
