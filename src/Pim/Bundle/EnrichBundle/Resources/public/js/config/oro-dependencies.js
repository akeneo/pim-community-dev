define([
    'oro/datagrid/string-cell',
    'oro/datagrid/html-cell',
    'oro/datagrid/date-cell',
    'oro/datagrid/navigate-action',
    'oro/datagrid/tab-redirect-action',
    'oro/datagrid/delete-action',
    'oro/datagrid/ajax-action',
    'oro/datagrid/mass-action'
], function(
    StringCell,
    HTMLCell,
    DateCell,
    NavigateAction,
    TabRedirectAction,
    DeleteAction,
    AjaxAction,
    MassAction
) {
    return {
        'oro/datagrid/string-cell': StringCell,
        'oro/datagrid/html-cell': HTMLCell,
        'oro/datagrid/date-cell': DateCell,
        'oro/datagrid/navigate-action': NavigateAction,
        'oro/datagrid/tab-redirect-action': TabRedirectAction,
        'oro/datagrid/delete-action': DeleteAction,
        'oro/datagrid/ajax-action': AjaxAction,
        'oro/datagrid/mass-action': MassAction
    }
});
