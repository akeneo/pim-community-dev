Feature: Filter on identifier
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by identifier

  Scenario: Successfully filter on identifier via the identifier attribute
    Given a "footwear" catalog configuration
    And the following products:
      | sku     |
      | BOOTBXS |
      | BOOTWXS |
      | BOOTBS  |
      | BOOTBL  |
      | MUGRXS  |
    Then I should get the following results for the given filters:
      | filter                                                                                                                             | result                                     |
      | [{"field":"sku", "operator":"STARTS WITH",      "value": "BOOT"   }]                                                               | ["BOOTBXS", "BOOTWXS", "BOOTBS", "BOOTBL"] |
      | [{"field":"sku", "operator":"STARTS WITH",      "value": "boot"   }]                                                               | ["BOOTBXS", "BOOTWXS", "BOOTBS", "BOOTBL"] |
      | [{"field":"sku", "operator":"ENDS WITH",        "value": "xs"     }]                                                               | ["BOOTBXS", "BOOTWXS", "MUGRXS"]           |
      | [{"field":"sku", "operator":"CONTAINS",         "value": "TB"     }]                                                               | ["BOOTBXS", "BOOTBS", "BOOTBL"]            |
      | [{"field":"sku", "operator":"DOES NOT CONTAIN", "value": "Boot"   }]                                                               | ["MUGRXS"]                                 |
      | [{"field":"sku", "operator":"=",                "value": "BOOTWXS"}]                                                               | ["BOOTWXS"]                                |
      | [{"field":"sku", "operator":"=",                "value": "MUGRXS "}]                                                               | []                                         |
      | [{"field":"sku", "operator":"!=",               "value": "BOOTBS"}]                                                                | ["BOOTBXS", "BOOTWXS", "BOOTBL", "MUGRXS"] |
      | [{"field":"sku", "operator":"IN", "value": ["BOOTBL", "MUGRXS"]}, {"field":"sku", "operator":"DOES NOT CONTAIN", "value": "BOOT"}] | ["MUGRXS"]                                 |
    
  Scenario: Successfully filter on identifier via the identifier field
    Given a "footwear" catalog configuration
    And the following products:
      | sku     |
      | BOOTBXS |
      | BOOTWXS |
      | BOOTBS  |
      | BOOTBL  |
      | MUGRXS  |
    Then I should get the following results for the given filters:
      | filter                                                                                                                                    | result                                     |
      | [{"field":"identifier", "operator":"STARTS WITH",      "value": "BOOT"   }]                                                               | ["BOOTBXS", "BOOTWXS", "BOOTBS", "BOOTBL"] |
      | [{"field":"identifier", "operator":"STARTS WITH",      "value": "boot"   }]                                                               | ["BOOTBXS", "BOOTWXS", "BOOTBS", "BOOTBL"] |
      | [{"field":"identifier", "operator":"ENDS WITH",        "value": "xs"     }]                                                               | ["BOOTBXS", "BOOTWXS", "MUGRXS"]           |
      | [{"field":"identifier", "operator":"CONTAINS",         "value": "TB"     }]                                                               | ["BOOTBXS", "BOOTBS", "BOOTBL"]            |
      | [{"field":"identifier", "operator":"DOES NOT CONTAIN", "value": "Boot"   }]                                                               | ["MUGRXS"]                                 |
      | [{"field":"identifier", "operator":"=",                "value": "BOOTWXS"}]                                                               | ["BOOTWXS"]                                |
      | [{"field":"identifier", "operator":"=",                "value": "MUGRXS "}]                                                               | []                                         |
      | [{"field":"identifier", "operator":"!=",               "value": "BOOTBS"}]                                                                | ["BOOTBXS", "BOOTWXS", "BOOTBL", "MUGRXS"] |
      | [{"field":"identifier", "operator":"IN", "value": ["BOOTBL", "MUGRXS"]}, {"field":"identifier", "operator":"DOES NOT CONTAIN", "value": "BOOT"}] | ["MUGRXS"]                                 |
