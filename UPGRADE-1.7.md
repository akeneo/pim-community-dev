# UPGRADE FROM 1.6 to 1.7

## Disclaimer

> Please check that you're using Akeneo PIM v1.6

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).


## Standard Normalizers

In order to use the standard format, Structured Normalizers have been replaced by Standard Normalizers. 

The following command helps to migrate references to these classes or services.
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\Catalog\\Normalizer\\Structured\\AttributeNormalizer/PimEnterprise\\Component\\Catalog\\Normalizer\\Standard\\AttributeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Structured\\AssetNormalizer/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Standard\\AssetNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Structured\\ChannelConfigurationNormalizer/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Standard\\ChannelConfigurationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Structured\\VariationNormalizer/PimEnterprise\\Component\\ProductAsset\\Normalizer\\Standard\\VariationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pimee_serializer\.normalizer\.structured\.attribute/pimee_catalog\.normalizer\.standard\.attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.structured\.asset/pimee_product_asset\.normalizer\.standard\.asset/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.structured\.variation/pimee_product_asset\.normalizer\.standard\.variation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.structured\.channel_configuration/pimee_product_asset\.normalizer\.standard\.channel_configuration/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_product_asset\.normalizer\.flat\.asset/pimee_product_asset\.normalizer\.flat\.asset/g'
```

## Rule structure modifications

### Metrics 

In the enrichment rules, the key "data" has been replaced by the key "amount" for metrics.

In 1.6 version, the rule structure was defined like this :

```
field: weight
operator: =
value:
 data: 0.5
 unit: KILOGRAM
```

In 1.7 version, the rule structure is defined like this :

```
field: weight
operator: =
value:
 amount: 0.5
 unit: KILOGRAM
```

### Prices 

In the enrichment rules, the key "data" has been replaced by the key "amount" for prices.

In 1.6 version, the rule structure was defined like this :

```
field: null_price
operator: NOT EMPTY
value:
  data: null
  currency: EUR
```

In 1.7 version, the rule structure is defined like this :

```
field: basic_price
operator: <=
value:
  amount: 12
  currency: EUR
```

### Pictures and files

In the enrichment rules, the rule structure has been changed for pictures and files.
The notion of original filename has been removed. The filename will be directly determined from the full path.

In 1.6 version, the rule structure was defined like this :

```
field: small_image
operator: CONTAIN
value:
  - filePath: /tmp/image.jpg
  - originalFilename: akeneo.jpg 
```

In 1.7 version, the rule structure is defined like this :

```
field: small_image
operator: CONTAIN
value: /tmp/image.jpg
```

According to the full path specified in this example, the filename will be "image.jpg".
