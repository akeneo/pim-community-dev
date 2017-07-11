

import Backbone from 'backbone'
import Routing from 'routing'
var UserContext = Backbone.Model.extend({
    url: Routing.generate('pim_user_user_rest_get_current')
})

var instance = new UserContext()

instance.fetch({async: false})

export default instance

