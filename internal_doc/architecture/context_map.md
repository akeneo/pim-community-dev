# What is a bounded context and a context map?

## Bounded context

> A bounded context represents a **functional perimeter**. 
> This is where a model is implemented and reflects a language spoken by every member of the team. 
> Typically, a bounded context belongs to only one team and is decoupled from the others. 

Today, that's not the case for us because our bounded contexts are coupled together. But this is the direction we'd like to go. We discovered several bounded contexts but **you should keep in mind that they are likely to evolve in the future!**  

## Context map

> A context map defines how bounded contexts communicate and are integrated together.

### How do we represent a relation?

![image](images/context_map/relation.svg)

- **U** means **Upstream**: this bounded context provides information to another one.
- **D** means **Downstream**: this bounded context uses information from another one.

### How can I update the context map?

All sketches are available [here](https://drive.google.com/drive/folders/1qAAjxexqvdvFJEeXp6Vb6LFSNCBVuTTL). Don't hesitate to change them if there is an error or if the relations evolve.

# How do we organize ourselves?

Each squad is in charge of several bounded contexts. 

![image](images/context_map/all_teams.svg)

This sketch shows the dependencies between squad:
- Raccoon squad is the only squad which only provides information to the other ones.
- Weasel is the only squad which which only uses information from the other ones.
- Chipmunk is the squad which provides and uses information to/from the other ones.

## Raccoon squad

The raccoon squad handles the **User Management**, **Channel**, **Asset** and **Enriched Entity** bounded contexts.

### Definitions

- **User Management**: Define the users, their preferences and their functional perimeter, so that it reflects the customer organization.
- **Channel**: The settings to distribute a product selection to a given market, based on some geographical parameters.
- **Asset**: Library of media shared by several products to bring added value and leverage the product experience.
- **Enriched Entity** (Enterprise Edition only): Structured and rich data shared by several products to bring added value and leverage the product experience.

### Context map

![image](images/context_map/raccoon.svg)

**Caution:** We still need to work on the dependencies of the User management bounded context.

## Chipmunk squad

The chipmunk squad handles the **User Management**, **Channel**, **Asset** and **Enriched Entity** bounded contexts.

### Definitions

- **Enrichment**: Bring added value to the products by enriching their information, and follow their quality to create the basis of a compelling product experience.
- **Structure**: The structure is the catalog foundations to modelize products. It should be tailored to customer needs.
- **Permissions**: (Enterprise Edition only): Allow for the separation of privileges by user group on the product information.

### Context map

![image](images/context_map/chipmunk.svg)

## Weasel squad

The weasel squad handles **Teamwork assistant**, **Workflow**, **Rule engine** and **Franklin Insight** bounded contexts. 
Those bounded contexts are related to two main topics which are **Automation** and **Work Organization**.

### Definitions

#### Automation

> Automatically enrich product information inside the PIM to increase users' productivity.

- **Rule engine**: Todo
- **Franklin Insight**: Todo

#### Work Organization

> Set of features that allows self-organization and collaboration with teammates in order to increase users' productivity.

- **Teamwork assistant** : Todo
- **Workflow**: Todo

### Context map

![image](images/context_map/weasel.svg)

## Do we cover all PIM code?

The answer is **No**. All teams are responsible of two other things: our tools and the platform.

- **Tool**: Technical libraries that could be used outside Akeneo.
- **Platform**: Everything that glues all the other bounded contexts together to make it a consistent application.
