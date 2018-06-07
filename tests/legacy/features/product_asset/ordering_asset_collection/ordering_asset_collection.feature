Feature: Order product assets inside asset collections
  In order to use ordered product assets in asset collections
  As a product manager
  I would like to order the assets linked to the products in the asset collection

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code   | type                  | group | reference_data_name |
      | photos | pim_assets_collection | other | assets              |
    And the following family:
      | code | attributes |
      | bags | photos     |
    And the following assets:
      | code   | categories         |
      | bridge | asset_main_catalog |
      | dog    | asset_main_catalog |
      | paint  | asset_main_catalog |

  Scenario: Product assets can be ordered in an asset collection
    Given the following product:
      | sku | family |
      | bag | bags   |
    When assets paint, bridge and dog are ordered into the asset collection photos of the product bag
    Then the asset collection photos of the product bag should be ordered as paint, bridge and dog

  Scenario: Changing the asset order in the asset collection creates a new version of the product
    Given the following product:
      | sku | family | photos           |
      | bag | bags   | paint,bridge,dog |
    When assets bridge, paint and dog are ordered into the asset collection photos of the product bag
    Then the last version of the product bag should be:
      | field  | old_value        | new_value        |
      | photos | paint,bridge,dog | bridge,paint,dog |

  Scenario: It is possible to revert the change of the asset order in the asset collection
    Given the following product:
      | sku | family | photos           |
      | bag | bags   | paint,bridge,dog |
    And assets bridge, paint and dog are ordered into the asset collection photos of the product bag
    When the product bag is reverted to the previous version
    Then the asset collection photos of the product bag should be ordered as paint, bridge and dog
