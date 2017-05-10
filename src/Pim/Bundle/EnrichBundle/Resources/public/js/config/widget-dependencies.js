define([
    'pim/dashboard/abstract-widget',
    'pim/dashboard/completeness-widget',
    'pim/dashboard/last-operations-widget',
    'pim/dashboard/widget-container'
], function(
    AbstractWidget,
    CompletenessWidget,
    LastOperationsWidget,
    WidgetContainer
) {
    return {
        'pim/dashboard/abstract-widget': AbstractWidget,
        'pim/dashboard/completeness-widget': CompletenessWidget,
        'pim/dashboard/last-operations-widget': LastOperationsWidget,
        'pim/dashboard/widget-container': WidgetContainer
    }
});
