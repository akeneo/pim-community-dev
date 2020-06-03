import React from 'react';
import { Select2Wrapper } from '../../../../components/Select2Wrapper';
import { Translate } from '../../../../dependenciesTools';
import { Action, AvailableAddAction } from '../../../../models/Action';

type Props = {
  translate: Translate;
  handleAddAction: (action: Action) => void;
};

const AddActionButton: React.FC<Props> = ({ translate, handleAddAction }) => {
  const [closeTick, setCloseTick] = React.useState<boolean>(false);

  const actionsData = Object.keys(AvailableAddAction).map(actionKey => {
    return {
      id: actionKey,
      text: translate(
        `pimee_catalog_rule.form.edit.actions.${actionKey}.title`
      ),
    };
  });

  const handleAddActionFromKey = (actionKey: string) => {
    const createActionFunction = AvailableAddAction[actionKey];
    const action = createActionFunction();
    handleAddAction(action);
  };

  return (
    <Select2Wrapper
      data={actionsData}
      id={'add-action-button'}
      label={translate('pimee_catalog_rule.form.edit.actions.add_action')}
      hiddenLabel={true}
      containerCssClass={'add-action-button'}
      dropdownCssClass={'add-action-dropdown'}
      onSelecting={(event: any) => {
        event.preventDefault();
        setCloseTick(!closeTick);
        handleAddActionFromKey(event.val);
      }}
      placeholder={translate('pimee_catalog_rule.form.edit.actions.add_action')}
      multiple={false}
    />
  );
};

export { AddActionButton };
