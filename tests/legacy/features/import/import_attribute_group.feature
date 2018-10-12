Feature: Import attribute groups
  In order to setup my application
  As an administrator
  I need to be able to import attribute groups

  Scenario: Successfully import new attribute group in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;6
      """
    When the attribute groups are imported via the job csv_footwear_attribute_group_import
    Then there should be the following attribute groups:
      | code          | label-en_US   | attributes                           | sort_order |
      | manufacturing | Manufacturing | lace_fabric,manufacturer,sole_fabric | 6          |

  Scenario: Successfully update existing attribute group and add a new one
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;6
      sizes;Size;size;3
      marketing;Marketing;sku;10
      other;Default;sku;200
      """
    When the attribute groups are imported via the job csv_footwear_attribute_group_import
    Then there should be the following attribute groups:
      | code          | label-en_US   | attributes                                                                                                  | sort_order |
      | manufacturing | Manufacturing | lace_fabric,manufacturer,sole_fabric                                                                        | 6          |
      | sizes         | Size          | size                                                                                                        | 3          |
      | marketing     | Marketing     | sku                                                                                                         | 10         |
      | other         | Default       | 123,cap_color,comment,destocking_date,handmade,heel_color,number_in_stock,price,rate_sale,rating,sole_color | 200        |

  @jira https://akeneo.atlassian.net/browse/PIM-7655
  Scenario: Ensure imported attribute groups have non conflicting sort order
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;label-en_US;attributes;sort_order
      manufacturing;Manufacturing;manufacturer,lace_fabric,sole_fabric;4
      sizes;Size;size;4
      marketing;Marketing;sku;6
      other;Default;sku;200
      """
    And the following job "csv_footwear_attribute_group_import" configuration:
      | filePath | %file to import% |
    When the attribute groups are imported via the job csv_footwear_attribute_group_import
    Then there should be the following attribute groups:
      | code          | label-en_US   | attributes                                                                                                  | sort_order |
      | manufacturing | Manufacturing | lace_fabric,manufacturer,sole_fabric                                                                        | 7          |
      | sizes         | Size          | size                                                                                                        | 8          |
      | marketing     | Marketing     | sku                                                                                                         | 6          |
      | other         | Default       | 123,cap_color,comment,destocking_date,handmade,heel_color,number_in_stock,price,rate_sale,rating,sole_color | 200        |
