@javascript
Feature: Well display navigation titles
  In order to have a well-formed title each the page
  As a user
  I need to be able to see title depending of the catalog page

  Scenario: Successfully display the page titles
    Given a "footwear" catalog configuration
    And a "sandals" product
    And I am logged in as "admin"
    Then the following pages should have the following titles:
      | page                                      | title                                        |
      | association types                         | Association types                            |
      | "X_SELL" association type                 | Association types Cross sell \| Edit         |
      | attributes                                | Product attributes                           |
      | attribute creation                        | Product attributes \| Create                 |
      | "size" attribute                          | Product attributes Size \| Edit              |
      | channels                                  | Channels                                     |
      | channel creation                          | Channels \| Create                           |
      | "tablet" channel                          | Channels Tablet \| Edit                      |
      | category tree creation                    | Category trees \| Create                     |
      | currencies                                | Currencies                                   |
      | exports                                   | Export management                            |
      | "footwear_product_export" export job      | Export Footwear product export \| Show       |
      | "footwear_product_export" export job edit | Export Footwear product export \| Edit       |
      | families                                  | Families                                     |
      | "boots" family                            | Families Boots \| Edit                       |
      | attribute group creation                  | Attribute groups \| Create                   |
      | "info" attribute group                    | Attribute groups Product information \| Edit |
      | imports                                   | Import management                            |
      | "footwear_product_import" import job      | Import Footwear product import \| Show       |
      | "footwear_product_import" import job edit | Import Footwear product import \| Edit       |
      | locales                                   | Locales                                      |
      | products                                  | Products                                     |
      | "sandals" product                         | Products sandals \| Edit                     |
      | variant groups                            | Variant groups                               |
      | "caterpillar_boots" variant group         | Variant groups Caterpillar boots \| Edit     |
      | product groups                            | Groups                                       |
      | "similar_boots" product group             | Groups Similar boots \| Edit                 |
      | group types                               | Group types                                  |
      | "RELATED" group type                      | Group types [RELATED] \| Edit                |
