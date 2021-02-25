# The Connectivity Connection bounded context

Connections allow our customers to plug third-parties into Akeneo PIM.

## Connection settings

The connection settings page displays credentials to use the REST API: a Client ID, a secret, and the username and password, customers should use to call the REST API.

It's also where users can:
- choose the connection label, logo, and flow type.
- enable the connection tracking. Connection tracking is used to track data in the Connection dashboard.
- set-up the user group and role that will be used for permissions application

**Features:** Settings,

## Connection monitoring

To give insights to our users about how they use the REST API, they can access the Connection dashboard that shows how many products have been created, updated or _sent_ in the past week.

The connection monitoring also gives information on business errors that happened during product synchronization with source connections.

**Features:** Audit, ErrorManagement
## Events API

Public API that defines the API Event requests sent to an external application in reaction to PIM Events.

**Features:** Webhook

- [Ubiquitous language]('./docs/events_api/ubiquitous_language.md)
- [Architecture](./docs/events_api/webhook_architecture.md)
