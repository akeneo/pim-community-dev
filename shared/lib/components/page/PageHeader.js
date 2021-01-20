var __makeTemplateObject = (this && this.__makeTemplateObject) || function (cooked, raw) {
    if (Object.defineProperty) { Object.defineProperty(cooked, "raw", { value: raw }); } else { cooked.raw = raw; }
    return cooked;
};
import React, { Children, cloneElement, isValidElement } from 'react';
import styled from 'styled-components';
import { Actions, Breadcrumb, Illustration, State, Title, UserActions } from './header';
var Header = styled.header(templateObject_1 || (templateObject_1 = __makeTemplateObject(["\n  position: sticky;\n  top: 0;\n  padding: 40px 40px 20px;\n  background: white;\n  z-index: 10;\n  height: 130px;\n"], ["\n  position: sticky;\n  top: 0;\n  padding: 40px 40px 20px;\n  background: white;\n  z-index: 10;\n  height: 130px;\n"])));
var LineContainer = styled.div(templateObject_2 || (templateObject_2 = __makeTemplateObject(["\n  display: flex;\n  justify-content: space-between;\n"], ["\n  display: flex;\n  justify-content: space-between;\n"])));
var MainContainer = styled.div(templateObject_3 || (templateObject_3 = __makeTemplateObject(["\n  flex-grow: 1;\n  display: flex;\n  justify-content: space-between;\n  flex-direction: column;\n  max-width: 100%;\n"], ["\n  flex-grow: 1;\n  display: flex;\n  justify-content: space-between;\n  flex-direction: column;\n  max-width: 100%;\n"])));
var ActionsContainer = styled.div(templateObject_4 || (templateObject_4 = __makeTemplateObject(["\n  display: flex;\n  align-content: baseline;\n"], ["\n  display: flex;\n  align-content: baseline;\n"])));
var buildHeaderElements = function (children, showPlaceholder) {
    var headerElements = {
        illustration: undefined,
        breadcrumb: undefined,
        title: undefined,
        state: undefined,
        actions: undefined,
        userActions: undefined,
    };
    Children.forEach(children, function (child) {
        if (!isValidElement(child)) {
            return;
        }
        switch (child.type) {
            case Illustration:
                headerElements.illustration = React.cloneElement(child);
                break;
            case Breadcrumb:
                headerElements.breadcrumb = React.cloneElement(child);
                break;
            case Title:
                headerElements.title = React.cloneElement(child, {
                    showPlaceholder: showPlaceholder,
                });
                break;
            case State:
                headerElements.state = React.cloneElement(child);
                break;
            case Actions:
                headerElements.actions = React.cloneElement(child);
                break;
            case UserActions:
                headerElements.userActions = React.cloneElement(child);
                break;
        }
    });
    if (headerElements.userActions !== undefined && headerElements.actions !== undefined) {
        headerElements.actions = cloneElement(headerElements.actions, {
            userActionVisible: true,
        });
    }
    return headerElements;
};
var PageHeader = function (_a) {
    var children = _a.children, showPlaceholder = _a.showPlaceholder;
    var _b = buildHeaderElements(children, showPlaceholder), illustration = _b.illustration, breadcrumb = _b.breadcrumb, title = _b.title, state = _b.state, actions = _b.actions, userActions = _b.userActions;
    return (React.createElement(Header, null,
        React.createElement(LineContainer, null,
            illustration,
            React.createElement(MainContainer, null,
                React.createElement("div", null,
                    React.createElement(LineContainer, null,
                        breadcrumb,
                        React.createElement(ActionsContainer, null,
                            userActions,
                            actions)),
                    React.createElement(LineContainer, null,
                        title,
                        state))))));
};
PageHeader.Actions = Actions;
PageHeader.Breadcrumb = Breadcrumb;
PageHeader.Illustration = Illustration;
PageHeader.UserActions = UserActions;
PageHeader.Title = Title;
PageHeader.State = State;
export { PageHeader };
var templateObject_1, templateObject_2, templateObject_3, templateObject_4;
//# sourceMappingURL=PageHeader.js.map