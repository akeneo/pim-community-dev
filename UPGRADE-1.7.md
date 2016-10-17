# UPGRADE FROM 1.6 to 1.7

## Disclaimer

> Please check that you're using Akeneo PIM v1.6

> We're assuming that you created your project from the standard distribution

> This documentation helps to migrate projects based on the Community Edition and the Enterprise Edition

> Please perform a backup of your database before proceeding to the migration. You can use tools like [mysqldump](http://dev.mysql.com/doc/refman/5.1/en/mysqldump.html) and [mongodump](http://docs.mongodb.org/manual/reference/program/mongodump/).

> Please perform a backup of your codebase if you don't use a VCS (Version Control System).


## Migrate your system requirements

## Migrate your standard project

## Migrate your custom code

### Global updates for any project

#### Update references to moved `Pim\Bundle\ConnectorBundle\Reader` business classes

In order to be more precise about the roles our existing file iterators have we renamed some existing classed as existing file iterators would only supports only tabular file format like CSV and XLSX.

Please execute the following commands in your project folder to update the references you may have to these classes:
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Connector\\Reader\\File\\FileIterator/Pim\\Component\\Connector\\Reader\\File\\FlatFileIterator/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_connector\.reader\.file\.file_iterator\.class/pim_connector\.reader\.file\.flat_file_iterator\.class/g'
```

#### Update references to the standardized `Pim\Component\Catalog\Normalizer\Standard` classes

In order to use standard format, Structured Normalizers have been replaced by Standard Normalizers. 

Originally, the Normalizer `Pim\Component\Catalog\Normalizer\Structured\GroupNormalizer` was used to normalize both Groups and Variant Groups.
This normalizer has been splitted in two distinct normalizers :

* `Pim\Component\Catalog\Normalizer\Standard\GroupNormalizer` is used to normalize Groups
* `Pim\Component\Catalog\Normalizer\Standard\VariantGroupNormalizer` is used to normalize Variant Groups

Therefore, it is not possible to migrate automatically the use of these classes in your custom code.

The following command helps to migrate references to the other Normalizer classes or services.
```
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AssociationTypeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AssociationTypeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AttributeGroupNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeGroupNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AttributeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\AttributeOptionNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\AttributeOptionNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\CategoryNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\CategoryNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ChannelNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\ChannelNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\CurrencyNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\CurrencyNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\DateTimeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\DateTimeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\FamilyNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\FamilyNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\FileNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\FileNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\GroupNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\GroupNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\GroupTypeNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\GroupTypeNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\LocaleNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\LocaleNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\MetricNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\MetricNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductAssociationsNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\AssociationsNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\ProductNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductPriceNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\PriceNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductPropertiesNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\PropertiesNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductValueNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ProductValueNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\ProductValuesNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\Product\\ProductValuesNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Catalog\\Normalizer\\Structured\\TranslationNormalizer/Pim\\Component\\Catalog\\Normalizer\\Standard\\TranslationNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/Pim\\Component\\Comment\\Normalizer\\Structured\\CommentNormalizer/Pim\\Component\\Comment\\Normalizer\\Standard\\CommentNormalizer/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.association_type/pim_catalog\.normalizer\.standard\.association_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.association_type\.class/pim_catalog\.normalizer\.standard\.association_type\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute/pim_catalog\.normalizer\.standard\.attribute/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute\.class/pim_catalog\.normalizer\.standard\.attribute\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute_group/pim_catalog\.normalizer\.standard\.attribute_group/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute_group\.class/pim_catalog\.normalizer\.standard\.attribute_group\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute_option/pim_catalog\.normalizer\.standard\.attribute_option/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.attribute_option\.class/pim_catalog\.normalizer\.standard\.attribute_option\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.category/pim_catalog\.normalizer\.standard\.category/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.category\.class/pim_catalog\.normalizer\.standard\.category\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.channel/pim_catalog\.normalizer\.standard\.channel/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.channel\.class/pim_catalog\.normalizer\.standard\.channel\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.datetime/pim_catalog\.normalizer\.standard\.datetime/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.datetime\.class/pim_catalog\.normalizer\.standard\.datetime\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.family/pim_catalog\.normalizer\.standard\.family/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.family\.class/pim_catalog\.normalizer\.standard\.family\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.group/pim_catalog\.normalizer\.standard\.group/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.group\.class/pim_catalog\.normalizer\.standard\.group\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product/pim_catalog\.normalizer\.standard\.product/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product\.class/pim_catalog\.normalizer\.standard\.product\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_properties/pim_catalog\.normalizer\.standard\.product\.properties/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_properties\.class/pim_catalog\.normalizer\.standard\.product\.properties\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_associations/pim_catalog\.normalizer\.standard\.product\.associations/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_associations\.class/pim_catalog\.normalizer\.standard\.product\.associations\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_values/pim_catalog\.normalizer\.standard\.product\.product_values/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_values\.class/pim_catalog\.normalizer\.standard\.product\.product_values\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_value/pim_catalog\.normalizer\.standard\.product\.product_value/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_value\.class/pim_catalog\.normalizer\.standard\.product\.product_value\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_price/pim_catalog\.normalizer\.standard\.product\.price/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.product_price\.class/pim_catalog\.normalizer\.standard\.product\.price\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.metric/pim_catalog\.normalizer\.standard\.product\.metric/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.metric\.class/pim_catalog\.normalizer\.standard\.product\.metric\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.file/pim_catalog\.normalizer\.standard\.file/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.file\.class/pim_catalog\.normalizer\.standard\.file\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.currency/pim_catalog\.normalizer\.standard\.currency/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.currency\.class/pim_catalog\.normalizer\.standard\.currency\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.group_type/pim_catalog\.normalizer\.standard\.group_type/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.group_type\.class/pim_catalog\.normalizer\.standard\.group_type\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.locale/pim_catalog\.normalizer\.standard\.locale/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.locale\.class/pim_catalog\.normalizer\.standard\.locale\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.label_translation/pim_catalog\.normalizer\.standard\.translation/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.label_translation\.class/pim_catalog\.normalizer\.standard\.translation\.class/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.comment/pim_comment\.normalizer\.standard\.comment/g'
    find ./src/ -type f -print0 | xargs -0 sed -i 's/pim_serializer\.normalizer\.comment\.class/pim_comment\.normalizer\.standard\.comment\.class/g'
```
