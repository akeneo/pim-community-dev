import React from 'react';
import { FallbackAction } from '../../../models/FallbackAction';
import { Translate } from '../../../dependenciesTools';
import { ActionTemplate } from './ActionTemplate';

type Props = {
  action: FallbackAction;
  translate: Translate;
};

const AddToCategoryActionLine: React.FC<Props> = ({ translate, action }) => {
  return (
    <ActionTemplate
      translate={translate}
      title={translate('pimee_catalog_rule.form.edit.add_to_category')}
      helper={translate('pimee_catalog_rule.form.helper.add_to_category')}
      srOnly={translate('pimee_catalog_rule.form.legend.add_to_category')}>
      {/* Not coded yet */}
      {JSON.stringify(action.json)}
    </ActionTemplate>
  );
};

export { AddToCategoryActionLine };
