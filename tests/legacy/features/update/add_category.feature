Feature: Add category fields
  In order to update products
  As an internal process or any user
  I need to be able to add a category to the categories field of a product

  Scenario: Successfully update the category field
    Given a "apparel" catalog configuration
    And the following products:
      | sku                    | categories        |
      | add_one_when_empty     |                   |
      | add_two_when_empty     |                   |
      | add_one_when_not_empty | women_2015_autumn |
      | add_two_when_not_empty | women_2015_autumn |

    Then I should get the following products after apply the following updater to it:
      | product                | actions                                                                           | result                                                          |
      | add_one_when_empty     | [{"type": "add_data", "field": "categories", "data": ["men_2013"]}]               | {"categories": ["men_2013"]}                                    |
      | add_two_when_empty     | [{"type": "add_data", "field": "categories", "data": ["men_2013", "women_2014"]}] | {"categories": ["men_2013", "women_2014"]}                      |
      | add_one_when_not_empty | [{"type": "add_data", "field": "categories", "data": ["men_2013"]}]               | {"categories": ["men_2013", "women_2015_autumn"]}               |
      | add_two_when_not_empty | [{"type": "add_data", "field": "categories", "data": ["men_2013", "women_2014"]}] | {"categories": ["men_2013", "women_2014", "women_2015_autumn"]} |
