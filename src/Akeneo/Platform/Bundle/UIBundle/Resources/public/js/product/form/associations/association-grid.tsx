import React, {useEffect, useRef} from 'react';
const Routing = require('routing');
const PageableCollection = require('oro/pageable-collection');
const DatagridState = require('pim/datagrid/state');
const requireContext = require('require-context');
const datagridBuilder = require('oro/datagrid-builder');

type AssociationGridProps = {
  name: string;
  conf: any;
  onSelectionChange: () => void;
};

const AssociationGrid = ({name: gridName, conf}: AssociationGridProps) => {
  const gridRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    let urlParams = conf.getInitialParams('PACK');
    urlParams.alias = gridName;
    urlParams.params = conf.getInitialParams('PACK');

    const datagridState = DatagridState.get(gridName, ['filters']);
    if (null !== datagridState.filters) {
      const collection = new PageableCollection();
      const filters = collection.decodeStateData(datagridState.filters);

      collection.processFiltersParams(urlParams, filters, gridName + '[_filter]');
    }

    $.get(Routing.generate('pim_datagrid_load', urlParams)).then(function(response: any) {
      let metadata = response.metadata;
      /* Next lines are related to PIM-6113 and need some comments.
       *
       * When you just saved a datagrid from the Product Edit Form, you will have an URL like
       * '/association-group-grid?...&associatedIds[]=1&associatedIds[]=2', in reference of the last
       * checked groups in the datagrid.
       *
       * The fact is that there is 2 places where these parameters are set: in the URL, and in the
       * datagrid state (state.parameters.associatedIds).
       *
       * If you do not drop the params of the URL (containing associatedIds array), you will have
       * a mix of 2 times the same variable, defined at 2 different places. This leads to a refreshed
       * datagrid with wrong checkboxes.
       *
       * To prevent this behavior, we removed the parameters passed in the URL before rendering the
       * grid, to only allow datagrid state parameters.
       */
      const queryParts = metadata.options.url.split('?');
      const url = queryParts[0];
      const queryString = decodeURIComponent(queryParts[1])
        .replace(/&?association-group-grid\[associatedIds\]\[\d+\]=\d+/g, '')
        .replace(/^&/, '');
      metadata.options.url = url + '?' + queryString;

      // gridRef.current?.dataset = {metadata: metadata, data: JSON.parse(response.data)};

      let gridModules = metadata.requireJSModules;
      gridModules.push('pim/datagrid/state-listener');
      gridModules.push('oro/datafilter-builder');
      gridModules.push('oro/datagrid/pagination-input');

      let resolvedModules: any = [];
      gridModules.forEach(function(module: any) {
        resolvedModules.push(requireContext(module));
      });

      datagridBuilder(resolvedModules);
    });
  }, []);

  return <div ref={gridRef}>{name}</div>;
};

export {AssociationGrid};
