## The Product Aggregate

### Overview

According to Martin Fowler, "A DDD aggregate is a cluster of domain objects that can be treated as a single unit."
(https://martinfowler.com/bliki/DDD_Aggregate.html).

For most objects, the aggregate definition is pretty simple: a User is a single unit that has its own properties, like
the user name, the login, the password.

But for our main object, i.e. the product, things are a little bit more complex, as the product itself has different parts
with pretty strong domain meaning. But still, we could define the product aggregate as the product object itself, with
all the other objects that belong to it.

As an example, a `MetricValue` object, that is part of the Product `ValueCollection`, has a `Metric` object. This `Metric`
object exclusively belongs to the value, which belongs to product itself. It's not shared and its life cycle is the one of
the product.

To the contrary, an `OptionValue` provides a reference to an `AttributeOption`. But the `AttributeOption` does not belong to the
`OptionValue` object: the option can be shared between different products and has its own life cycle.

Same thing for the `Attribute`. Each product value has a reference to an attribute, but the attribute doesn't belong to any
product value.

See http://www.cqrs.nu/Faq/aggregates for even more details.

### Aggregate Root

The Product itself is the root of the aggregate. "Any references from outside the aggregate should only go to the aggregate root. The root can thus ensure the integrity of the aggregate as a whole.", Martin Fowler.

### Definition

Here is the product aggregate, with the objects that belong to it (scalars are not shown):

#### Objects belonging to the aggregate

```
Product
    - associations: Collection
        - Association
    - values: ValueCollection
        - DateValue
            - data: DateTime
        - MediaValue
            - data: FileInfo
        - MetricValue
            - data: Metric
        - OptionValue
        - OptionsValue
        - PriceCollectionValue
            - data: PriceCollection
                - Price
        - ScalarValue
        - ReferenceDataValue
        - ReferenceDataCollectionValue
    - updated: DateTime
    - created: DateTime
```

#### References to object outside of the aggregate

```
Product:
    - categories: references to many Categories
    - associations: Collection
        - Association
            - type: reference to one AssociationType
            - product: reference to a Product
            - group: reference to a Group
    - groups: references to many Groups
    - values: ValueCollection
        - *Value:
            - attribute: reference to an Attribute
            - locale: reference to a Locale
            - scope: reference to a channel
        - OptionValue
            - data: reference to an AttributeOption
        - OptionsValue
            - data: references to many AttributeOption
        - PriceCollection
            - Price
                - currency: reference to a Currency
        - ReferenceDataValue
            - data: reference to a ReferenceData
        - ReferenceDataCollectionValue
            - data: reference to many ReferenceData
    - family: reference to a Family
    - familyVariant: reference to a FamilyVariant
    - parent: reference to a ProductModel
```

### Development impacts

Defining the product aggregate gives a us a clearer view of responsibilities and references between objects.
And this has some technical impacts:

 -  Transaction

When persisting all the parts of the aggregate, this must be done in one transaction. Indeed, if one object
of the aggregate cannot be persisted, then the whole aggregate must be rejected, as it's not valid anymore.

 - Accessing referenced objects outside of the aggregate

For objects that are not part of the aggregate, we reference them by their identifiers. For example,
`$myValue->getAttributeCode()`.
If a service needs to get the object behind the reference, it can use the associated repository.

### What about completenesses?

Completenesses represent statistics of the product data state, according to conditions defined at
family level.

If we had an ideal storage system, completeness would not need to be pre-computed and would be used
directly in filters and computed from the persistence system in real time.

But as our technical stack doesn't allow this, we need to compute them when products are saved, so
they can be used for filtering.

They are basically a projection, so they don't belong to the product aggregate, and will be persisted
during a different transaction.

### The special case of MediaValue and FileInfo.

Technically speaking the `FileInfo` object can be shared between different products, as each time we upload
a file, a checksum is computed and if another file already exists with this content, a reference is created between
the existing file and the new product value.

This behaviour only exists for technical reasons, so it doesn't have an impact on the aggregate itself.

In the future, we may remove this behavior, to simplify the code as well as avoid confusing functional behavior.
