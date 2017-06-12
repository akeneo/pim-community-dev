@javascript
Feature: Well display navigation titles
  In order to have a well-formed title each the page
  As an administrator
  I need to be able to see title depending of the catalog page

  Scenario Outline: Successfully display the page titles
    Given a "footwear" catalog configuration
    And a "sandals" product
    And a "'quote'" product
    And I am logged in as "Peter"
    When I am on the <page> page
    Then I should see the title "<title>"

    Examples:
      | page                                          | title                                               |
      | association types                             | Association types                                   |
      | "X_SELL" association type                     | Association types Cross sell \| Edit                |
      | attributes                                    | Attributes                                          |
      | "size" attribute                              | Attributes Size \| Edit                             |
      | channels                                      | Channels                                            |
      | "tablet" channel                              | Channels Tablet \| Edit                             |
      | channel creation                              | Channels \| Create                                  |
      | categories                                    | Category trees                                      |
      | category tree creation                        | Category trees \| Create                            |
      | currencies                                    | Currencies                                          |
      | families                                      | Families                                            |
      | "boots" family                                | Families Boots \| Edit                              |
      | attribute groups                              | Attribute groups                                    |
      | attribute group creation                      | Attribute groups \| Create                          |
      | "info" attribute group                        | Attribute groups Product information \| Edit        |
      | locales                                       | Locales                                             |
      | products                                      | Products                                            |
      | "caterpillar_boots" variant group             | Variant groups Caterpillar boots \| Edit            |
      | product groups                                | Groups                                              |
      | "similar_boots" product group                 | Groups Similar boots \| Edit                        |
      | group types                                   | Group types                                         |
      | "RELATED" group type                          | Group types [RELATED] \| Edit                       |
      | exports                                       | Export profiles management                          |
      | "csv_footwear_product_export" export job      | Export profiles CSV footwear product export \| Show |
      | "csv_footwear_product_export" export job edit | Export profiles CSV footwear product export \| Edit |
      | imports                                       | Import profiles management                          |
      | "csv_footwear_product_import" import job      | Import profiles CSV footwear product import \| Show |
      | "csv_footwear_product_import" import job edit | Import profiles CSV footwear product import \| Edit |
      | import executions                             | Import executions history                           |
      | export executions                             | Export executions history                           |
      | variant groups                                | Variant groups                                      |
      | "sandals" product                             | Products sandals \| Edit                            |
      | "'quote'" product                             | Products 'quote' \| Edit                             |
