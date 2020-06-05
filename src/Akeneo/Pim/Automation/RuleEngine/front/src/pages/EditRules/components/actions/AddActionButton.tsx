import React from 'react';
import { Select2Wrapper } from '../../../../components/Select2Wrapper';
import { Action, AvailableAddAction } from '../../../../models/Action';
import { useTranslate } from '../../../../dependenciesTools/hooks';

type Props = {
  handleAddAction: (action: Action) => void;
};

const AddActionButton: React.FC<Props> = ({ handleAddAction }) => {
  const translate = useTranslate();
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
    console.log({ actionKey });
    const createActionFunction = AvailableAddAction[actionKey];
    const action = createActionFunction();
    console.log({ action });
    handleAddAction(action);
  };

  console.log({ actionsData });

  const addCategoryAction = {
    id: 'add_categories',
    text: 'Add categories',
  };

  return (
    <Select2Wrapper
      data={[...actionsData, addCategoryAction]}
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
