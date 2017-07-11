

import Backbone from 'backbone'
import Routing from 'routing'
var DateContext = Backbone.Model.extend({
    url: Routing.generate('pim_localization_format_date')
})

var instance = new DateContext()

instance.fetch({async: false})

export default instance

