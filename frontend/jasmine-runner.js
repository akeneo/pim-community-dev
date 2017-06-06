var requireAll = function(requireContext) {
    requireContext.keys().map(requireContext)
}

requireAll(require.context('./dynamic/', true, /^.*\/Bundle\/.*\/Resources\/public\/spec\/.*\/.*\.js$/))

Backbone.$ = $
