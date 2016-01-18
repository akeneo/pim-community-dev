Feature: Update category fields
  In order to update products
  As an internal process or any user
  I need to be able to update the category field of a product

  Scenario: Successfully update the category field
    Given a "apparel" catalog configuration
    And the following products:
      | sku                                     |
      | 2013_collection                         |
      | men_2013                                |
      | men_2013_and_women_2014                 |
      | none                                    |
      | women_2015_autumn_and_women_2015_winter |
      | men_2014_revert                         |
    Then I should get the following products after apply the following updater to it:
      | product                                 | actions                                                                                                                      | result                                                     |
      | 2013_collection                         | [{"type": "set_data", "field": "categories", "data": ["2013_collection"]}]                                                   | {"categories": ["2013_collection"]}                        |
      | men_2013                                | [{"type": "set_data", "field": "categories", "data": ["men_2013"]}]                                                          | {"categories": ["men_2013"]}                               |
      | men_2013                                | [{"type": "set_data", "field": "categories", "data": ["men_2013", "women_2014"]}]                                            | {"categories": ["men_2013", "women_2014"]}                 |
      | none                                    | [{"type": "set_data", "field": "categories", "data": []}]                                                                    | {"categories": []}                                         |
      | women_2015_autumn_and_women_2015_winter | [{"type": "set_data", "field": "categories", "data": ["women_2015_autumn", "women_2015_winter"]}]                            | {"categories": ["women_2015_autumn", "women_2015_winter"]} |
      | men_2014_revert                         | [{"type": "set_data", "field": "categories", "data": ["men_2014"]}, {"type": "set_data", "field": "categories", "data": []}] | {"categories": []}                                         |
