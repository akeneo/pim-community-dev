define(['oro/datagrid/delete-action', 'pimee/rule-manager'], function(DeleteAction, RuleManager) {
  return DeleteAction.extend({
    doDelete() {
      this.model.on('destroy', () => RuleManager.familyAttributesRulesNumberPromise = null);

      DeleteAction.prototype.doDelete.apply(this, arguments);
    },
  });
});
