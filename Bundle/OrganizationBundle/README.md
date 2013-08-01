OroOrganizationBundle
========================
The `OroOrganizationBundle` introduced 2 entities: Organization and Business Units that will help with data
responsibility and configuration.

Organization can have multiple business units assigned.

Each Business Unit must have parent organization assigned and can have a parent Business Unit.

Each User can be assigned to multiple business units. A business units tree on user update page was added for easy assignment.
