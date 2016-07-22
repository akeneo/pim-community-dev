EventStore event examples
--------------------------
I have an event subscriber exactly like yours.


#### Example of a mass edit product: action change status. 
I disabled 2 products. As you can see the change set is the previous from when I created the product. 
```json
{
  "family": "sofa_variation",
  "groups": [],
  "variant_group": null,
  "categories": [],
  "enabled": false,
  "values": {
    "sku": [
      {
        "locale": null,
        "scope": null,
        "data": "TESTING-1"
      }
    ],
    "variation_parent_product": [
      {
        "locale": null,
        "scope": null,
        "data": "UK-SKUMAGENTO000003"
      }
    ],
    "main_color": [
      {
        "locale": null,
        "scope": null,
        "data": "black"
      }
    ]
  },
  "associations": [],
  "sku": "TESTING-1",
  "changeset": {
    "sku": {
      "old": "",
      "new": "TESTING-1"
    },
    "family": {
      "old": "",
      "new": "sofa_variation"
    },
    "main_color": {
      "old": "",
      "new": "black"
    },
    "variation_parent_product": {
      "old": "",
      "new": "UK-SKUMAGENTO000003"
    },
    "enabled": {
      "old": "",
      "new": 1
    }
  },
  "author": {
    "name": "Iulian Popa",
    "email": "iulian.popa@made.com"
  }
}
```
In the change set I should have just one change, something like this:
```json
"changeset": {
    "enabled": {
      "old": 1,
      "new": 0
    }
}
```
But because the flush on the product is not done I got the wrong change set.
Or maybe because the build of the version is triggered after post_save, maybe that is a problem.

## Example of a mass edit product: action "Classify products in categories".
 Again the change set is the previous one because the object is not flushed.
```json
{
  "family": "sofa_variation",
  "groups": [],
  "variant_group": null,
  "categories": [
    "sofa"
  ],
  "enabled": true,
  "values": {
    "sku": [
      {
        "locale": null,
        "scope": null,
        "data": "TESTING-1"
      }
    ],
    "variation_parent_product": [
      {
        "locale": null,
        "scope": null,
        "data": "UK-SKUMAGENTO000003"
      }
    ],
    "main_color": [
      {
        "locale": null,
        "scope": null,
        "data": "black"
      }
    ]
  },
  "associations": [],
  "sku": "TESTING-1",
  "changeset": {
    "enabled": {
      "old": 1,
      "new": 0
    }
  },
  "author": {
    "name": "Iulian Popa",
    "email": "iulian.popa@made.com"
  }
}
```
I made some comments as an example on the Pim\Bundle\CatalogBundle\Doctrine\Common\Saver\ProductSaver.
Please let me know if you have any questions.