define([], function () {
    return function() {
        // return the module from the already loaded webpack dependencies
        return window[name] || {}
    }
})
