# How to activate/deactivate ACL/roles depending on a feature flag


This mini documentation explains how to activate/deactivate ACL depending of a feature flags. As a reminder, ACL is defined for the roles in the PIM. It determines if an action is possible or not for a user (create a family for example).


## Technical explanation

## ACL

ACL are defined in `Resources/config/acl.yml` by convention. To enable an ACL if a feature flag is activated, just configure the key `feature` with the associated feature flag:

Example for the product rules:
```
pimee_catalog_rule_rule_execute_permissions:
    type: action
    label: pimee_catalog_rule.acl.rule.execute_permissions
    group_name: pimee_catalog_rule.acl_group.rule
    feature: product_rules
```

Note: they key `feature` is not mandatory. If not provided, the ACL is enabled.

## ACL group

ACL groups are defined in `Resources/config/acl_group.yml` by convention. ACL group is a notion that only exist in frontend (no notion in database). Therefore, deactivating a group *does not* deactivate ACLs in this group. It only put these ACLs in "System" group in the UI. 

To display a group in the UI depending on a feature flag, just configure the `feature` key:
```
pimee_catalog_rule.acl_group.rule:
    order: 130
    feature: product_rules

```


## Impacts

The ACLs and ACLs are not displayed in the UI. Also, the action defined by the ACLs cannot be executed by the user.
