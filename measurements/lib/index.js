import React, { useEffect } from 'react';
import { HashRouter as Router, Route, Switch } from 'react-router-dom';
import { List } from './pages/list';
import { Edit } from './pages/edit';
import { ConfigContext } from './context/config-context';
import { UnsavedChangesContext } from './context/unsaved-changes-context';
import { useDependenciesContext } from '@akeneo-pim-community/legacy';
var value = {
    hasUnsavedChanges: false,
    setHasUnsavedChanges: function (newValue) {
        value.hasUnsavedChanges = newValue;
    },
};
var Index = function () {
    var mediator = useDependenciesContext().mediator;
    useEffect(function () {
        if (mediator) {
            mediator.trigger('pim_menu:highlight:tab', { extension: 'pim-menu-settings' });
            mediator.trigger('pim_menu:highlight:item', { extension: 'pim-menu-measurements-settings' });
        }
    }, [mediator]);
    return (React.createElement(ConfigContext.Provider, { value: { operations_max: 5, units_max: 50, families_max: 300 } },
        React.createElement(UnsavedChangesContext.Provider, { value: value },
            React.createElement(Router, { basename: "/configuration/measurement" },
                React.createElement(Switch, null,
                    React.createElement(Route, { path: "/:measurementFamilyCode" },
                        React.createElement(Edit, null)),
                    React.createElement(Route, { path: "/" },
                        React.createElement(List, null)))))));
};
export default Index;
//# sourceMappingURL=index.js.map