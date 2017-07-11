
import router from 'pim-router'
import __ from 'oro/translator'
var routeParams = {}

router.on('route_complete', function (name) {
  document.title = __('page_title.' + name, routeParams)
})

export default {
  set: function (params) {
    routeParams = params
  }
}
