

import $ from 'jquery'
import _ from 'underscore'
import FormRegistry from 'pim/form-registry'
var buildForm = function (formName) {
    return $.when(
                FormRegistry.getForm(formName),
                FormRegistry.getFormMeta(formName),
                FormRegistry.getFormExtensions(formName)
            ).then(function (Form, formMeta, extensionMeta) {
                var form = new Form(formMeta)
                form.code = formName

                var extensionPromises = []
                _.each(extensionMeta, function (extension) {
                    var extensionPromise = buildForm(extension.code)
                    extensionPromise.done(function (loadedModule) {
                        extension.loadedModule = loadedModule
                    })

                    extensionPromises.push(extensionPromise)
                })

                return $.when.apply($, extensionPromises).then(function () {
                    _.each(extensionMeta, function (extension) {
                        form.addExtension(
                            extension.code,
                            extension.loadedModule,
                            extension.targetZone,
                            extension.position
                        )
                    })

                    return form
                })
            })
}

export default {
    build: function (formName) {
        return buildForm(formName).then(function (form) {
            return form.configure().then(function () {
                return form
            })
        })
    },

    buildForm: buildForm
}

