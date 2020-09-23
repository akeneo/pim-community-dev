define(
    ['oro/datagrid/ajax-action', 'oro/messenger', 'oro/translator'],
    function(AjaxAction, messenger, __) {
    return AjaxAction.extend({
        /**
         * {@inheritdoc}
         */
        getMethod: function () {
            return 'PUT';
        },

        /**
         * {@inheritdoc}
         */
        getActionParameters: function() {
            return JSON.stringify({ enabled: !this.model.get('enabled') });
        },

        /**
         * {@inheritdoc}
         */
        _onAjaxSuccess: function(data, textStatus, jqXHR) {
            this.datagrid.hideLoading();
            this.datagrid.collection.fetch();

            messenger.notify('success', this.model.get('enabled')
                ? __('flash.rule.disabled')
                : __('flash.rule.enabled')
            );
        },
    });
});
