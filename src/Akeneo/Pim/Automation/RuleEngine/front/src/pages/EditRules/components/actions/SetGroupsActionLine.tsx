import React from 'react';
import { ActionLineProps } from './ActionLineProps';
import { Controller } from 'react-hook-form';
import { useControlledFormInputAction } from '../../hooks';
import { GroupCode } from '../../../../models';
import { ActionTemplate } from './ActionTemplate';
import {
  useTranslate,
  useBackboneRouter,
} from '../../../../dependenciesTools/hooks';
import { ActionTitle, AknActionFormContainer } from './ActionLine';
import { GroupsSelector } from '../../../../components/Selectors/GroupsSelector';
import { getGroupsByIdentifiers } from '../../../../repositories/GroupRepository';

export const SetGroupsActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();

  const {
    fieldFormName,
    typeFormName,
    valueFormName,
    getValueFormValue,
  } = useControlledFormInputAction<GroupCode[]>(lineNumber);

  const [unexistingGroupCodes, setUnexistingGroupCodes] = React.useState<
    GroupCode[]
  >([]);

  React.useEffect(() => {
    const groupCodes: GroupCode[] = getValueFormValue();

    if (!groupCodes?.length) {
      setUnexistingGroupCodes([]);
      return;
    }

    getGroupsByIdentifiers(groupCodes, router).then(groups => {
      setUnexistingGroupCodes(
        groupCodes.filter((groupCode: GroupCode) => !groups[groupCode])
      );
    });
  }, []);

  const validateGroupCodes = (groupCodes: GroupCode[]) => {
    if (!groupCodes?.length) {
      return translate('pimee_catalog_rule.exceptions.required');
    }

    const unknownGroupCodes: GroupCode[] = groupCodes.filter(
      (groupCode: GroupCode) => unexistingGroupCodes.includes(groupCode)
    );

    return (
      !unknownGroupCodes.length ||
      translate(
        'pimee_catalog_rule.exceptions.unknown_groups',
        {
          groupCodes: unknownGroupCodes.join(', '),
        },
        unknownGroupCodes.length
      )
    );
  };

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='groups'
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='set'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_groups.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.set_groups.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.set_groups.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionTitle>
          {translate(
            'pimee_catalog_rule.form.edit.actions.set_groups.subtitle'
          )}
        </ActionTitle>
        <AknActionFormContainer>
          <Controller
            as={GroupsSelector}
            id={`edit-rules-actions-${lineNumber}-value`}
            label={`${translate(
              'pim_enrich.mass_edit.product.operation.add_to_group.field'
            )} ${translate('pim_common.required_label')}`}
            currentCatalogLocale={currentCatalogLocale}
            value={getValueFormValue()}
            rules={{ validate: validateGroupCodes }}
            name={valueFormName}
          />
        </AknActionFormContainer>
      </ActionTemplate>
    </>
  );
};
