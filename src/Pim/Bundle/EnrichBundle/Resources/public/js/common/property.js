define([], function () {
    return {
        accessProperty: function (data, path, defaultValue) {
            defaultValue = defaultValue || null;
            var pathPart = path.split('.');

            if (undefined === data[pathPart[0]]) {
                return defaultValue;
            }

            return 1 === pathPart.length ?
                data[pathPart[0]] :
                this.accessProperty(data[pathPart[0]], pathPart.slice(1).join('.'), defaultValue);
        },

        updateProperty: function (data, path, value) {
            var pathPart = path.split('.');

            data[pathPart[0]] = 1 === pathPart.length ?
                value :
                this.updateProperty(data[pathPart[0]], pathPart.slice(1).join('.'), value, defaultValue)

            return data;
        }
    }
})
