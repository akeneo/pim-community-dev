# Decisions

_This document keeps track of all the discussions and decisions we took on the subject and should be updated each time we discuss about a subject worth remembering._

## 10/08/2018

### Validation of commands

Right now we are validating the command given by the action to give feedback to the user. In the case of the creation of an attribute, the user cannot edit all properties. The backend define default values for those properties during the creation of the command and then we validate it.

**This situation can lead to some problems**: if the backend is not well coded, we can be in a situation when the user get messages about fields that he cannot edit.

**Solution**: we think that maybe we should have different validation groups (one for the creation and one for the edition) and define the default values deeper in the stack (the command handler maybe). That way, there will be no chance that the user get errors on fields that he cannot manage.

### Strategy of tests for validation

The validation is an important subject for the testing strategy: it's involving both the backend and the frontend and the contract between them is key. Right now, we are testing the validation with integration tests with in memory repositories for the backend and acceptance tests in the frontend.

**Problem with this solution**: this is really verbose and we are testing all validation cases using http requests. Also the responses are not shared between the frontend and the backend which can lead to mis-alignment.

**Solution**: We think that we should use JSON files to share the responses expected by the backend and the frontend. To do so, we will have one file per response with to mandatory keys: code (string) and body (JSON). This work is potentially huge so we can say that for the next cards we will implement this technique and see how it goes.

## 12/08/2018

### Attribute model (REJECTED)

Currently we have a different model for each attribute type with different signature for all the additional properties. This situation is not really representative of the feature: additional properties are treated separatly in the form and are not shared between attribute types. Also, this implies to have dedicated services during the whole chaine: normalization/denormalization, commands, repositories, savers, reducers, hydrators, etc. Note that this part of the application could be extensible and this situation could be really complicated to manage.

**Problem**: We don't want to manage different model shape for each attribute type, therefore, we want to be sure that all attribute types have the properties they should have.

**Proposition**: I'm not sure if it's the best solution, but maybe we should have a collection of additional properties in the attribute. We will keep the attribute model responsible of their normalization and denormalization but at least, the interfact of the attributes will not change between types.

Simplyfied models to see the difference:

Before:

    ImageAttribute {
      setIdentifier()
      setLabel(locale: string)
      setRequired()
      setMaxFileSize();
      setAllowedExtensions();
      normalize();
    }

After:

    ImageAttribute {
      setIdentifier()
      setLabel(locale: string)
      setRequired()
      setAdditionalProperties(additionalProperties: TextAdditionalProperties)
      normalize();
    }

With the second solution, all attribute type would have the same signature or really close signatures.

### Attribute deletion

Right now in the PIM, it's really hard to manage attribute deletion. If we remove it, it's fine and everything works. But if we re-create an attribute with the same code but not the same scope/locale/type, it can leads to big problems. Dealing with a big clean job can be really complicated and weird for the user experience.

**Problem**: We need to find a solution to deal with attribute deletion that is not costly in term of performances and can avoid future problems.

**Solution**: The idea is to store the important attribute informations alongside with the value:

    {
      attribute: 'description',
      type: 'text',
      valuePerLocale: false,
      valuePerChannel: true,
      value: 'My awesome description
    }

This way, when we hydrate a value from the db, we are able to tell right away if it's in sync with the current attribute type/scope/locale and reject it if needed. This will clearly be a bit more costly to load but it's totally errorproof and should solve the problem we have on this topic. This solution should be benchmarked to be sure that it's not too costly.

This decision should be discussed with Alex

## 16/08/2018

### Attribute deletion

Follow up of [attribute deletion](#attribute-deletion): after some benchmarks, the read operations (mysql, hydration, normalization) are almost free compared to symfony bootup. On a macbook air, it takes 100ms to load 25 attributes while it takes 110ms to load 250 attributes.

We also discussed about a way to invalidate more easily the values of a record after an attribute deletion:

#### First solution: unique hash generated at attribute creation

We store this hash on the values. Each time we load a value, we compare this hash to the actual attribute and if it's different, it means that it has been deleted and re-created.
Pros: Simple solution to implmement, the same for all attribute type and efficient in term of performances.
Cons: If the user delete the attribute and re-create it with the same code, same type and same scopability/localizability, the values will be invalidated whereas they could have been restored.

#### Second solution: sha1 of the attribute type, localizable and scopable

At attribute creation, we calculate a hash of the "structure property" of the attribute into a hash and use it exaclty like the previous solution (store in value+xompare at hydration).
The problem with this solution is that all attribute type does not have the same "structural properties". For example, if you delete a metric attribute and recreate it with the same code, same type, same scope but another metric family, the hydration of the values will fail. So for each attribute type, the `getHash` method could be different.

Pros: Efficient in term of performance, the user can recover data if he deleted the attribute by error.
Cons: require more code and different for each attribute types.
