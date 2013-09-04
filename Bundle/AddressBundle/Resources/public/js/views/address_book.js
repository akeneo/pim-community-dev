var OroAddressBook = Backbone.View.extend({
    options: {
        'mapOptions': {
            zoom: 17
        },
        'template': null,
        'addressListUrl': null,
        'addressCreateUrl': null,
        'addressUpdateUrl': null,
        'mapView': OroMapView.googlemaps
    },

    attributes: {
        'class': 'map-box'
    },

    initialize: function() {
        this.options.collection = this.options.collection || new OroAddressCollection();
        this.options.collection.url = this._getUrl('addressListUrl');

        this.listenTo(this.getCollection(), 'activeChange', this.activateAddress);
        this.listenTo(this.getCollection(), 'add', this.addAddress);
        this.listenTo(this.getCollection(), 'reset', this.addAll);
        this.listenTo(this.getCollection(), 'remove', this.onAddressRemove);

        this.$adressesContainer = Backbone.$('<div class="map-address-list"/>').appendTo(this.$el);
        this.$mapContainerFrame = Backbone.$('<div class="map-visual-frame"/>').appendTo(this.$el);
        this.mapView = new this.options.mapView({
            'mapOptions': this.options.mapOptions,
            'el': this.$mapContainerFrame
        });
    },

    _getUrl: function(optionsKey) {
        if (_.isFunction(this.options[optionsKey])) {
            return this.options[optionsKey].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        return this.options[optionsKey];
    },

    getCollection: function() {
        return this.options.collection;
    },

    onAddressRemove: function() {
        if (!this.getCollection().where({active: true}).length) {
            var primaryAddress = this.getCollection().where({primary: true});
            if (primaryAddress.length) {
                primaryAddress[0].set('active', true);
            } else if (this.getCollection().length) {
                this.getCollection().at(0).set('active', true);
            }
        }
    },

    addAll: function(items) {
        this.$adressesContainer.empty();
        items.each(function(item) {
            this.addAddress(item);
        }, this);
        this._activatePreviousAddress();
    },

    _activatePreviousAddress: function() {
        if (this.activeAddress !== undefined) {
            var previouslyActive = this.getCollection().where({id: this.activeAddress.get('id')});
            if (previouslyActive.length) {
                previouslyActive[0].set('active', true);
            }
        }
    },

    addAddress: function(address) {
        var addressView = new OroAddressView({
            model: address
        });
        addressView.on('edit', _.bind(this.editAddress, this));
        this.$adressesContainer.append(addressView.render().$el);
    },

    editAddress: function(addressView, address) {
        this._openAddressEditForm(
            _.__('Update Address'),
            this._getUrl('addressUpdateUrl', address)
        );
    },

    createAddress: function() {
        this._openAddressEditForm(
            _.__('Add Address'),
            this._getUrl('addressCreateUrl')
        );
    },

    _openAddressEditForm: function(title, url) {
        if (!this.addressEditDialog) {
            this.addressEditDialog = Oro.widget.Manager.createWidget('dialog', {
                'url': url,
                'title': title,
                'stateEnabled': false,
                'incrementalPosition': false,
                'dialogOptions': {
                    'modal': true,
                    'resizable': false,
                    'width': 400,
                    'autoResize':true,
                    'close': _.bind(function() {
                        delete this.addressEditDialog;
                    }, this)
                }
            });
            this.addressEditDialog.render();
            Oro.Events.bind(
                "hash_navigation_request:start",
                _.bind(function () {
                    if (this.addressEditDialog) {
                        this.addressEditDialog.remove();
                    }
                }, this)
            );
            this.addressEditDialog.on('formSave', _.bind(function() {
                this.addressEditDialog.remove();
                Oro.NotificationFlashMessage('success', _.__('Address successfully saved'));
                this.reloadAddresses();
            }, this));
        }
    },

    reloadAddresses: function() {
        this.getCollection().fetch({reset: true});
    },

    activateAddress: function(address) {
        if (!address.get('primary')) {
            this.activeAddress = address;
        }
        this.mapView.updateMap(address.getSearchableString(), address.get('label'));
    }
});
