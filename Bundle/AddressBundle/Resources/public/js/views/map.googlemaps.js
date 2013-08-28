var OroMapView = OroMapView || {};
OroMapView.googlemaps = Backbone.View.extend({
    options: {
        mapOptions: {
            zoom: 17,
            mapTypeControl: true,
            panControl: false,
            zoomControl: true
        },
        apiVersion: '3.exp',
        sensor: false,
        apiKey: null
    },

    mapLocationCache: {},
    mapsLoadExecuted: false,

    initialize: function() {
        this.$mapContainer = Backbone.$('<div class="map-visual"/>')
            .appendTo(this.$el);
        this.$unknownAddress = Backbone.$('<div class="map-unknown">' + _.__('map.unknown.location') + '</div>')
            .appendTo(this.$el);
        this.mapLocationUnknown();
    },

    _initMapOptions: function() {
        if (_.isUndefined(this.options.mapOptions.mapTypeControlOptions)) {
            this.options.mapOptions.mapTypeControlOptions = {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
            };
        }
        if (_.isUndefined(this.options.mapOptions.zoomControlOptions)) {
            this.options.mapOptions.zoomControlOptions = {
                style: google.maps.ZoomControlStyle.SMALL
            };
        }
        if (_.isUndefined(this.options.mapOptions.mapTypeId)) {
            this.options.mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP;
        }
    },

    _initMap: function(location) {
        this._initMapOptions();
        this.map = new google.maps.Map(
            this.$mapContainer[0],
            _.extend({}, this.options.mapOptions, {center: location})
        );

        this.mapLocationMarker = new google.maps.Marker({
            draggable: false,
            map: this.map,
            position: location
        });
    },

    loadGoogleMaps: function() {
        var googleMapsSettings = 'sensor=' + (this.options.sensor ? 'true' : 'false');
        if (this.options.apiKey) {
            googleMapsSettings += '&key=' + this.options.apiKey
        }

        Backbone.$.ajax({
            url: window.location.protocol + "//www.google.com/jsapi",
            dataType: "script",
            cache: true,
            success: _.bind(function() {
                google.load('maps', this.options.apiVersion, {
                    other_params: googleMapsSettings,
                    callback: _.bind(this.onGoogleMapsInit, this)
                });
            }, this)
        });
    },

    updateMap: function(address, label) {
        // Load google maps js
        if (!this.hasGoogleMaps() && !this.mapsLoadExecuted) {
            this.mapsLoadExecuted = true;
            this.requestedLocation = {
                'address': address,
                'label': label
            };
            this.loadGoogleMaps();

            return;
        }

        if (this.mapLocationCache.hasOwnProperty(address)) {
            this.updateMapLocation(this.mapLocationCache[address], label);
        } else {
            this.getGeocoder().geocode({'address': address}, _.bind(function(results, status) {
                if(status == google.maps.GeocoderStatus.OK) {
                    this.mapLocationCache[address] = results[0].geometry.location;
                    //Move location marker and map center to new coordinates
                    this.updateMapLocation(results[0].geometry.location, label);
                } else {
                    this.mapLocationUnknown();
                }
            }, this));
        }
    },

    onGoogleMapsInit: function() {
        if (!_.isUndefined(this.requestedLocation)) {
            this.updateMap(this.requestedLocation.address, this.requestedLocation.label);
            delete this.requestedLocation;
        }
    },

    hasGoogleMaps: function() {
        return !_.isUndefined(window.google) && google.hasOwnProperty('maps');
    },

    mapLocationUnknown: function() {
        this.$mapContainer.hide();
        this.$unknownAddress.show();
    },

    mapLocationKnown: function() {
        this.$mapContainer.show();
        this.$unknownAddress.hide();
    },

    updateMapLocation: function(location, label) {
        this.mapLocationKnown();
        if (location && (!this.location || location.toString() != this.location.toString())) {
            this._initMap(location);
            this.map.setCenter(location);
            this.mapLocationMarker.setPosition(location);
            this.mapLocationMarker.setTitle(label);
            this.location = location;
        }
    },

    getGeocoder: function() {
        if (_.isUndefined(this.geocoder)) {
            this.geocoder = new google.maps.Geocoder();
        }
        return this.geocoder;
    }
});
