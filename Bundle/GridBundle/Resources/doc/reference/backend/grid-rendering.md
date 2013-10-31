Grid Rendering
--------------

OroGridBundle has a couple of classes that can be used to render grid results.

#### Class Description

* **Twig \ GridRendererExtension** - a Twig extension that can be used to render datagrid results as JSON in Twig template;
* **Renderer \ GridRenderer** - a class that can be used to render datagrid responses.

#### Example of Usage

Use renderer in controller to render JSON response of grid:

``` php
<?php

namespace Bar\FooBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BarController extends Controller
{
    public function listAction(Request $request)
    {
        $gridManager = $this->get('bar_grid_manager');
        $datagrid = $gridManager->getDatagrid();
        $datagridView = $datagrid->createView();
        if ('json' == $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }
        return $this->render('Foo:Bar:list.html.twig', array('datagrid' => $datagridView));
    }
}
```

Use Twig extension function to get JSON object of grid's results in javascript:

```
<script type="text/javascript">
    var results = {{ oro_grid_render_results_json(datagridView) }};
</script>
```
