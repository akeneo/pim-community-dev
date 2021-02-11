# Events API Ubiquitous Language

* **Events API**

    Public API that defines the API Event requests sent to an external application in reaction to PIM Events.

* **Connection**

    Connections allow our customers to plug third-parties into Akeneo PIM.

    It contains the credentials to use the REST API: a Client ID, a secret, and the username and password, customers should use to call the REST API

* **Event Subscription**

    Represents the configuration of a Connection to be able to send API Events through the Events API.

    The subscription includes a destination URL and a "secret" (to verify the requests authenticity).

* **PIM Event**

    Lifecycle event of the PIM entities.

* **API Event**

    Event sent by the Events API to an Event Subscription.

    > For example, for a `product.created` event, it will contains the whole Product data with the permissions of the Connection applied to it.

* **API Event Type**

    Event Type identify each kind of API Event offered through the Events API.

    For example `product.updated`, `product.removed`, `product_model.created`.

* **Events API Request**

    Request sent to an Event Subscription URL, it contains a collection of API Events as well as the required headers to valid the Request authenticity (signature, timestamp).

* **Queue**

    The Queue allows to store & send Messages to another process to be handled asynchronously at a later time.

* **Message**

    Messages contains the data published in the Queue.

    The data is application agnostic, in this case messages are formated in JSON.
