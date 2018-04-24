Feature: Remove category fields
  In order to update products
  As an internal process or any user
  I need to be able to remove a category of a product

  Scenario: Successfully remove the category
    Given a "apparel" catalog configuration
    And the following products:
      | sku                          | categories                |
      | 2014_collection              | 2014_collection           |
      | 2013_collection              | 2013_collection           |
      | men_2013_and_women_2014      | men_2013, women_2014      |
      | men_2013_and_2013_collection | men_2013, 2013_collection |
      | none                         |                           |
      | men_2014_and_2014_collection | men_2014, 2014_collection |
    Then I should get the following products after apply the following updater to it:
      | product                      | actions                                                                                                                                             | result                              |
      | 2013_collection              | [{"type": "remove_data", "field": "categories", "data": ["2013_collection"]}]                                                                       | {"categories": []}                  |
      | 2014_collection              | [{"type": "remove_data", "field": "categories", "data": []}]                                                                                        | {"categories": ["2014_collection"]} |
      | men_2013_and_women_2014      | [{"type": "remove_data", "field": "categories", "data": ["men_2013", "women_2014"]}]                                                                | {"categories": []}                  |
      | men_2013_and_2013_collection | [{"type": "remove_data", "field": "categories", "data": ["men_2013"]}]                                                                              | {"categories": ["2013_collection"]} |
      | none                         | [{"type": "remove_data", "field": "categories", "data": ["men_2013"]}]                                                                              | {"categories": []}                  |
      | men_2014_and_2014_collection | [{"type": "remove_data", "field": "categories", "data": ["men_2014"]}, {"type": "remove_data", "field": "categories", "data": ["2014_collection"]}] | {"categories": []}                  |
