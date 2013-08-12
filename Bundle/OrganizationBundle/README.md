OroOrganizationBundle
========================
The `OroOrganizationBundle` introduced 2 entities: Organization and Business Units that will help with data
responsibility and configuration.

Organization can have multiple business units assigned.

Each Business Unit must have parent organization assigned and can have a parent Business Unit.

Each User can be assigned to multiple business units. A business units tree on user update page was added for easy assignment.

### Entity Ownerships

Each entity can have one of 4 ownership types defined: None, User, Business Unit or Organization.

Ownership type is stored in entity config and can be defined through entity class annotation

``` php
/**
    ...
 * @Configurable(
 *  defaultValues={
 *      "entity"={"label"="User", "plural_label"="Users"},
 *      "acl"={"owner_type"="BUSINESS_UNIT"}
 *  }
 * )
    ...
 */
 class User
```

<table>
<tr>
    <th>Label</th>
    <th>Code</th>
</tr>
<tr>
    <td>None</td>
    <td>NONE</td>
</tr>
<tr>
    <td>User</td>
    <td>USER</td>
</tr>
<tr>
    <td>Business Unit</td>
    <td>BUSINESS_UNIT</td>
</tr>
<tr>
    <td>Organization</td>
    <td>ORGANIZATION</td>
</tr>
</table>

Based on entity ownership type, entity record owner is automatically saved using current user data.
