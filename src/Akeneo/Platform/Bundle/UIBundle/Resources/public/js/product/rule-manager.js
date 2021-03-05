'use strict';

define(['jquery', 'underscore', 'routing'], function($, _, Routing) {
  return {
    familyAttributesRulesNumberPromise: null,
    getFamilyAttributesRulesNumber: function(attributeCodes) {
      if (this.familyAttributesRulesNumberPromise) {
        return this.familyAttributesRulesNumberPromise.promise();
      }

      this.familyAttributesRulesNumberPromise = $.Deferred();

      $.getJSON(Routing.generate('pimee_enrich_family_attributes_rules_number', {attributes: attributeCodes})).done(
        function(familyCode) {
          this.familyAttributesRulesNumberPromise.resolve(familyCode);
        }.bind(this)
      );

      return this.familyAttributesRulesNumberPromise.promise();
    },
  };
});
