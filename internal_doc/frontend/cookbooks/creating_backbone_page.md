# Creating a new Backbone page

This is a quick start to add a new Backbone page to the PIM.

For more explanations take a look at:
- [Akeneo PIM Frontend Guide (medium.com/akeneo-labs)](https://medium.com/akeneo-labs/akeneo-pim-frontend-guide-part-1-bd398b6483a2)
- (Old) Public documention [Design the user interfaces (docs.akeneo.com)](https://docs.akeneo.com/latest/design_pim/index.html)

## Create the controller

## Configure the route

## Create the page





```yml
# CustomUIBundle/Resources/config/requirejs.yml

config:
  config:
    pim/product/index:
      title: "Product index page"
  paths:
    pim/product/index: customui/js/product/index.ts
```

```ts
// CustomUIBundle/Resources/public/js/product/index.ts

const View = require("pim/form");

class Index extends View {}

export = Index;
```
