@javascript
Feature: Well display navigation titles
  In order to have a well-formed title each the page
  As an administrator
  I need to be able to see title depending of the catalog page

  Background:
    Given a "footwear" catalog configuration
    And a "sandals" product
    And a "'quote'" product
    And I am logged in as "Peter"

  Scenario Outline: Successfully display the page titles
    When I am on the <page> page
    Then I should see the title "<title>"

    Examples:
      | page                                          | title                                               |
      | association types                             | Association types                                   |
      | "X_SELL" association type                     | Association type Cross sell \| Edit                 |
      | attributes                                    | Attributes                                          |
      | "size" attribute                              | Attribute Size \| Edit                              |
      | channels                                      | Channels                                            |
      | "tablet" channel                              | Channel Tablet \| Edit                              |
      | channel creation                              | Channels \| Create                                  |
      | categories                                    | Category trees                                      |
      | category tree creation                        | Category trees \| Create                            |
      | currencies                                    | Currencies                                          |
      | families                                      | Families                                            |
      | "boots" family                                | Family Boots \| Edit                                |
      | attribute groups                              | Attribute groups                                    |
      | attribute group creation                      | Attribute groups \| Create                          |
      | "info" attribute group                        | Attribute group Product information \| Edit         |
      | locales                                       | Locales                                             |
      | products                                      | Products                                            |
      | product groups                                | Groups                                              |
      | "similar_boots" product group                 | Group Similar boots \| Edit                         |
      | group types                                   | Group types                                         |
      | "RELATED" group type                          | Group type [RELATED] \| Edit                        |
      | exports                                       | Export profiles management                          |
      | "csv_footwear_product_export" export job      | Export profile CSV footwear product export \| Show  |
      | "csv_footwear_product_export" export job edit | Export profile CSV footwear product export \| Edit  |
      | imports                                       | Import profiles management                          |
      | "csv_footwear_product_import" import job      | Import profile CSV footwear product import \| Show  |
      | "csv_footwear_product_import" import job edit | Import profile CSV footwear product import \| Edit  |
      | "sandals" product                             | Product sandals \| Edit                             |
      | "'quote'" product                             | Product 'quote' \| Edit                             |
