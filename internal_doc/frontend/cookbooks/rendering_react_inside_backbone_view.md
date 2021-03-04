# How to render a React component in a Backbone view?

![Preview](./images/cookbook_develop_with_react_in_backbone_view.gif)

## Render a React component in a backbone view

```jsx
import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {MyView} from './MyView';

const BaseView = require('pimui/js/view/base');

class MyBridgeView extends BaseView {
    // .. configure, override your view

    public render() {
        // ... do something before rendering React component

        // Pass the props you need to your React component (optional)
        const myProps = {/* ... */};

        ReactDOM.render(
            <DependenciesProvider>
                <ThemeProvider theme={pimTheme}>
                    <MyView {...myProps}/>
                </ThemeProvider>
            </DependenciesProvider>,
            this.el
        );

        // ... do something after rendering React component

        return this;
    }

    public remove() {
        // You need to unmount your React component when the module is removed
      ReactDOM.unmountComponentAtNode(this.el);

      return super.remove();
    }
}

export = MyBridgeView;
```

## A simpler way to render a React component in a backbone view.

The dependencies and the unmount of your component are managed by the BaseView

```jsx
import {MyView} from './MyView';

const BaseView = require('pimui/js/view/base');

class MyBridgeView extends BaseView {
    // .. configure, override your view

    public render() {
        // ... do something before rendering React component

        // Pass the props you need to your React component (optional)
        const myProps = {/* ... */};

        this.renderReact(MyView, myProps, this.el);

        // ... do something after rendering React component

        return this;
    }
}

export = MyBridgeView;
```
