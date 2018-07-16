# Architecture

## Bounded contexts

According to Eric Evans in [Domain-Driven Design Reference](https://domainlanguage.com/ddd/reference/), a bounded context is: 

*"A description of a boundary (typically a subsystem, or the work of a particular team) within which a particular model is defined and applicable."*

Typically, a bounded context belongs to only one team, relates to only one topic (within which the ubiquituous language is used) and is decoupled from the others. Today, that's not the case for us. But this is the direction we'd like to go.

Here is the list of our bounded contexts:

- *User Management*: Enable enterprises to manage users from a central directory.
- *Channel*: Allow the configuration of the markets on which the information (product, asset, related entities information) will be distributed
- *Asset*: Allow users to manage the library of assets related to products.
- *Enriched Entity* (Enterprise Edition only): Enriched entities related to products with their own properties and lifecycle.
- *PIM*: Centralize and harmonize all the information required to market and sell products through distribution channels.
    - *PIM/Enrichment*: Enable to create/update products to improve and follow their quality.
    - *PIM/Structure*: Define the product catalog foundations.
    - *PIM/Automation* (Enterprise Edition only): Automatically create/update product information.
    - *PIM/Permissions* (Enterprise Edition only): Allow for the separation of privileges by user group on the product information.
    - *PIM/Work Organization* (Enterprise Edition only): Enable self-organization and collaboration with coworkers.

