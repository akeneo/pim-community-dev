define(['oro/datagrid/rules-action', 'pimee/rule-manager'], function(RulesMassAction, RuleManager) {
  return RulesMassAction.extend({
    _onAjaxSuccess: function (data, textStatus, jqXHR) {
      RuleManager.familyAttributesRulesNumberPromise = null;

      RulesMassAction.prototype._onAjaxSuccess.apply(this, arguments);
    },
  });
});
