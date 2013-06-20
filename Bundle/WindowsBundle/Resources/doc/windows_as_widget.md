Windows as Widget
========================

### @Template annotation handling

WindowsBundle added next logic to View rendering logic.
In case when widget container is specified with _widgetContainer request parameter then ```<container>.<template.name>```
will be searched, if no template for all widget types ```widget.<template.name>``` will be searched.
If no such then default template will be used.

Example. Called URL /test/create?_widgetContainer=dialog which handled by TestBundle:SomeController:createAction.

By default in Symfony2 template name will be guessed as TestBundle:Some:create.html.twig

In our case when _widgetContainer=dialog

- TestBundle:Some:dialog.create.html.twig will be used if exists
- TestBundle:Some:widget.create.html.twig will be used if exists
- TestBundle:Some:create.html.twig will be used by default in case when no container specific or widget default templates found
