import $ from 'jquery';
import _ from 'underscore';
import Routing from 'routing';


        /**
         * @return {Object}
         */
export default {
    collect: function (route) {
        return $.getJSON(Routing.generate(route));
    }
};

