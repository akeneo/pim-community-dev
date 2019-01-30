# Architecture

## You said "Bounded contexts"?

According to Eric Evans in [Domain-Driven Design Reference](https://domainlanguage.com/ddd/reference/), a bounded context is:

*"A description of a boundary (typically a subsystem, or the work of a particular team) within which a particular model is defined and applicable."*

Typically, a bounded context belongs to only one team, relates to only one topic (within which the ubiquituous language is used) and is decoupled from the others. Today, that's not the case for us. But this is the direction we'd like to go.

To go towards this direction, we began to split the code into big set of features, located in the `src/Akeneo` directory. Here they are:

- *User Management*: Enable enterprises to manage users from a central directory.
- *Channel*: Allow the configuration of the markets on which the information (product, asset, related entities information) will be distributed
- *Asset*: Allow users to manage the library of assets related to products.
- *Enriched Entity* (Enterprise Edition only): Enriched entities related to products with their own properties and lifecycle.
- *PIM*: Centralize and harmonize all the information required to market and sell products through distribution channels.
    - *PIM/Enrichment*: Enable to create/update products to improve and follow their quality.
    - *PIM/Structure*: Define the product catalog foundations.
    - *PIM/Automation* (Enterprise Edition only): Automatically create/update product information.
    - *PIM/Automation/FranklinInsights* (Enterprise Edition only): Franklin Insights is an intelligence layer whose mission is to guide Julia across the PIM to focus on compelling PX
    - *PIM/Permissions* (Enterprise Edition only): Allow for the separation of privileges by user group on the product information.
    - *PIM/Work Organization* (Enterprise Edition only): Enable self-organization and collaboration with coworkers.
- *Tool*: Technical libraries that could be used outside Akeneo.
- *Platform*: Everything that glues all the other contexts together to make it a consistent application.

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
