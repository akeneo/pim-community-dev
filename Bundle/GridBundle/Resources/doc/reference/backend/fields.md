Fields
------

Field Description is an entity that contains all information about one grid column - name, type, filter/sorter flags etc. Filter Descriptions are stored in Field Description Collection.

#### Class Description

* **Field \ FieldDescriptionInterface** - basic interface for Field Description, provides setters an getters for field parameters and options;
* **Field \ FieldDescription** - Field Description implementation of basic interface, has method to extract field value from source object;
* **Field \ FieldDescriptionCollection** - storage for FieldDescription entities, implements ArrayAccess, Countable and IteratorAggregate interfaces and their methods.
