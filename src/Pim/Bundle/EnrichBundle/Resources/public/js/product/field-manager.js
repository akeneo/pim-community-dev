/**
 * Field manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import FetcherRegistry from 'pim/fetcher-registry'
import ConfigProvider from 'pim/form-config-provider'
import requireContext from 'require-context'

var fields = {}
var visibleFields = {}
var loadedModules = {}

var getFieldForAttribute = function (attribute) {
  var deferred = $.Deferred()

  if (loadedModules[attribute.field_type]) {
    deferred.resolve(loadedModules[attribute.field_type])

    return deferred.promise()
  }

  ConfigProvider.getAttributeFields().done(function (attributeFields) {
    var fieldModule = attributeFields[attribute.field_type]
    if (!fieldModule) {
      throw new Error('No field defined for attribute type "' + attribute.field_type + '"')
    }

    var ResolvedModule = requireContext(fieldModule)
    loadedModules[attribute.field_type] = ResolvedModule
    deferred.resolve(ResolvedModule)
  })

  return deferred.promise()
}

export default {
  getField: function (attributeCode) {
    var deferred = $.Deferred()

    if (fields[attributeCode]) {
      deferred.resolve(fields[attributeCode])

      return deferred.promise()
    }

    FetcherRegistry.getFetcher('attribute').fetch(attributeCode).done(function (attribute) {
      getFieldForAttribute(attribute).done(function (Field) {
        fields[attributeCode] = new Field(attribute)
        deferred.resolve(fields[attributeCode])
      })
    })

    return deferred.promise()
  },
  getNotReadyFields: function () {
    var notReadyFields = []

    _.each(fields, function (field) {
      if (!field.isReady()) {
        notReadyFields.push(field)
      }
    })

    return notReadyFields
  },
  getFields: function () {
    return fields
  },
  addVisibleField: function (attributeCode) {
    visibleFields[attributeCode] = fields[attributeCode]
  },
  getVisibleFields: function () {
    return visibleFields
  },
  getVisibleField: function (attributeCode) {
    return visibleFields[attributeCode]
  },
  clearFields: function () {
    fields = {}
  },
  clearVisibleFields: function () {
    visibleFields = {}
  }
}
