# Events API Ubiquitous Language


* **Events API**

    Public API that defines the API Event requests sent to an external application in reaction to PIM Events.

* **Connection**

    Configuration of the credentials and permissions required to work with the PIM Rest API and Events API.

* **Event Subscription**

    Represents the configuration of a Connection to be able to send API Events through the Events API.

    The configuration includes a destination URL and a "secret" (to verify the requests authenticity).

* **PIM Event**

    Lifecycle event of the PIM entities.

    Like `product.created` for the creation of a new Product.

* **API Event**

    Event sent by the Events API to an Event Subscription while respecting the Connection permissions.

    It contains the PIM Event informations as well as the full event data following the Events API specifications.

    For example, for a `product.created` event, it will contains the whole Product data with the permissions of the Connection applied to it.

* **Event Type**

    Event Type identify each kind of API Event offered through the Events API.

    For example `product.updated`, `product.removed`, `product_model.created`.

* **Request**

    Request sent to an Event Subscription URL, it contains a collection of API Events as well as the required headers to valid the Request authenticity (signature, timestamp).

* **Bulk**

    Group of related object, like the **PIM Events Bulk**.

* **Queue**

    The Queue allows to store & send Messages to another process to be handled asynchronously at a later time.

* **Message**

    Messages contains the data published in the Queue.

    The data is application agnostic, in this case messages are formated in JSON.
