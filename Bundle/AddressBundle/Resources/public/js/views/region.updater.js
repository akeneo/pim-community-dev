Oro = Oro || {};
Oro.RegionUpdater = Oro.RegionUpdater || {};

Oro.RegionUpdater.View = Backbone.View.extend({
    events: {
        'change': 'selectionChanged'
    },

    /**
     * Constructor
     *
     * @param options {Object}
     */
    initialize: function (options) {
        this.target = $(options.target);
        this.$simpleEl = $(options.simpleEl);

        this.target.closest('.controls').append(this.$simpleEl);
        this.$simpleEl.attr('type', 'text');

        this.template = $('#region-chooser-template').html();

        this.listenTo(this.collection, 'reset', this.render);
    },

    /**
     * Trigger change event
     */
    sync: function () {
        if (this.target.val() == '' && $(this.el).val() != '') {
            $(this.el).trigger('change');
        }
    },

    /**
     * onChange event listener
     *
     * @param e {Object}
     */
    selectionChanged: function (e) {
        var countryId = $(e.currentTarget).val();
        this.collection.setCountryId(countryId);
        this.collection.fetch();
    },

    render: function() {
        if (this.collection.models.length > 0) {
            this.target.show();
            $('#uniform-' + this.target[0].id).show();

            this.target.val('').trigger('change');
            this.target.find('option[value!=""]').remove();
            this.target.append(_.template(this.template, {regions: this.collection.models}));

            this.$simpleEl.hide();
            this.$simpleEl.val('');
        } else {
            this.target.hide();
            $('#uniform-' + this.target[0].id).hide();
            this.$simpleEl.show();
        }
    }
});
