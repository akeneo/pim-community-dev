# Use React portals to share a context between multiple React instances

You might want to use a React application to override a page and display some components in different parts (dropZone) of the page (header, content, navigation, ...). Also, you might want to share a same context or props with these components.

Using React portals can be a solution

![Preview](./images/cookbook_develop_with_react_portals.gif)

## Activate the view in the PIM

Define the path for requireJs:

```yaml
config:
  paths:
    # ...
    path-to-the-view/my-view: a-public-assets-symfony-bundle/js/view/MyBridgeView.ts
    path-to-the-view/my-portal-view: a-public-assets-symfony-bundle/js/view/MyBridgePortalView.ts
    # ...
```

Configure the form extension:

```yaml
extensions:
  # ...

  ## My View Example
    pim-product-edit-form-my-view:
        module: akeneo/data-quality-insights/view/my-view
        parent: pim-product-edit-form
        targetZone: content
        position: 130 # the main view has to be display at last

    pim-product-edit-form-my-portal-view:
        module: akeneo/data-quality-insights/view/my-portal-view
        parent: pim-product-edit-form
        targetZone: content
        position: 30 # components rendered in portal have to be rendered before the main view

  # ...
```

## Use Portals for displaying several React components sharing the same context in a page

```tsx
import { MyView } from "./MyView";

const BaseView = require("pimui/js/view/base");

class MyBridgeView extends BaseView {
  public updateIdentifier(identifier: string): void {
    const data = this.getFormData();
    this.setData({
      ...data,
      identifier,
    });

    this.render();
  }

  public render() {
    this.renderReact(
      MyView,
      {
        product: this.getFormData(),
        updateIdentifier: this.updateIdentifier.bind(this), // Binding with `this` scope is important here
      },
      this.el
    );

    return this;
  }
}

export = MyBridgeView;
```

```tsx
const BaseView = require("pimui/js/view/base");

class MyBridgePortalView extends BaseView {
  public render() {
    this.el.insertAdjacentHTML(
      "beforeend",
      `
      <div id="my-portal-container"></div>
    `
    );

    return this;
  }
}

export = MyBridgePortalView;
```

```tsx
import React, { FC, useCallback } from "react";
import { Button } from "akeneo-design-system";
import { MyPortalView } from "./MyPortalView";

type Product = {
  identifier: string;
  // ...
};

type Props = {
  product: Product;
  updateIdentifier: (identifier: string) => void;
};

const MyView: FC<Props> = ({ product, updateIdentifier }) => {
  const generateIdentifier = useCallback(() => {
    const identifier = Math.random().toString(36).substring(7);
    updateIdentifier(identifier);
  }, [updateIdentifier]);

  return (
    <>
      <div>
        MY PRODUCT IDENTIFIER: <strong>{product.identifier}</strong>
        &nbsp;
        <Button onClick={generateIdentifier}>Generate identifier</Button>
      </div>
      <MyPortalView product={product} generateIdentifier={generateIdentifier} />
    </>
  );
};

export { MyView };
```

```tsx
import React, { FC } from "react";
import { createPortal } from "react-dom";
import { Button } from "akeneo-design-system";

type Product = {
  identifier: string;
  // ...
};

type Props = {
  product: Product;
  generateIdentifier: () => void;
};

const MyContent: FC<Props> = ({ product, generateIdentifier }) => {
  return (
    <div>
      MY PRODUCT IDENTIFIER IN A PORTAL: <strong>{product.identifier}</strong>
      &nbsp;
      <Button onClick={generateIdentifier}>Generate identifier</Button>
    </div>
  );
};

const MyPortalView: FC<Props> = (props) => {
  const portalContainer = document.getElementById("my-portal-container");

  return (
    portalContainer && createPortal(<MyContent {...props} />, portalContainer)
  );
};

export { MyPortalView };
```
