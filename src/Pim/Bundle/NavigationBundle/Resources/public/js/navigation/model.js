/* global define */
import _ from 'underscore';
import routing from 'routing';
import Backbone from 'backbone';
    

    /**
     * @export  oro/navigation/model
     * @class   oro.navigation.Model
     * @extends Backbone.Model
     */
    export default Backbone.Model.extend({
        defaults: {
            title: '',
            url: null,
            position: null,
            type: null
        },

        url: function() {
            var base = _.result(this, 'urlRoot') || _.result(this.collection, 'url');
            if (base && base.indexOf(this.get('type')) === -1) {
                base += (base.charAt(base.length - 1) === '/' ? '' : '/') + this.get('type');
            } else if (!base) {
                base = routing.generate('oro_api_get_navigationitems', { type: this.get('type') });
            }
            if (this.isNew()) {
                return base;
            }
            return base + (base.charAt(base.length - 1) === '/' ? '' : '/') + 'ids/' + encodeURIComponent(this.id);
        }
    });

