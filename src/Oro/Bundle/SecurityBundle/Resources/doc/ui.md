UI
========

Current UI implementation allows to define permissions for user roles. For Entities it's managed with Entity/Permission matrix, for Capabilities, it's just a list of available resources.

If a user has several roles assigned, and we are checking user's permission to some resource, if **any** of user's roles grants access to this resource then access is also granted to this user.
