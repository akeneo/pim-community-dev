import React from 'react';
import { AddToCategoryAction } from '../../../../models/actions';
import { ActionTemplate } from './ActionTemplate';
import { ActionLineProps } from './ActionLineProps';

type Props = {
  action: AddToCategoryAction;
} & ActionLineProps;

const AddToCategoryActionLine: React.FC<Props> = ({
  translate,
  lineNumber,
  action,
  handleDelete,
  router,
  currentCatalogLocale,
}) => {
  console.log('lineNumber', lineNumber);
  console.log('action', action);
  console.log('router', router);
  console.log('currentCatalogLocale', currentCatalogLocale);

  return (
    <ActionTemplate
      translate={translate}
      title={translate('pimee_catalog_rule.form.edit.add_to_category')}
      helper={translate('pimee_catalog_rule.form.helper.add_to_category')}
      legend={translate('pimee_catalog_rule.form.legend.add_to_category')}
      handleDelete={handleDelete}>
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          padding: '5px',
        }}>
        <fieldset style={{ width: '50%' }}>
          <legend
            style={{ color: '#9452BA', fontSize: '15px', padding: '10px 0' }}>
            Select your category trees
          </legend>
          <div>Category tree (required)</div>
        </fieldset>
        <fieldset style={{ width: '50%' }}>
          <legend
            style={{ color: '#9452BA', fontSize: '15px', padding: '10px 0' }}>
            Select your categories for master catalog
          </legend>
          <div>Categories (required)</div>
        </fieldset>
      </div>
    </ActionTemplate>
  );
};

export { AddToCategoryActionLine };
