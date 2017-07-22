
import _ from 'underscore'
export default {
  /**
   * Get the form name corresponding to the specified attribute type, or null.
   *
   * @param {String} attributeType
   * @param {String} mode
   *
   * @return {String}
   */
  getFormName: function (attributeType, mode) {
    return _.has(__moduleConfig.formNames, attributeType)
      ? __moduleConfig.formNames[attributeType][mode]
      : null
  }
}
