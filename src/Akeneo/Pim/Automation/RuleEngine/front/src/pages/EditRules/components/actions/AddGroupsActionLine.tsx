import React from 'react';
import { Controller } from 'react-hook-form';
import { ActionLineProps } from './ActionLineProps';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { ActionTemplate } from './ActionTemplate';
import { useControlledFormInputAction } from '../../hooks';
import { GroupCode } from '../../../../models';
import {
  ActionGrid,
  ActionTitle,
  AknActionFormContainer,
  ActionLeftSide,
} from './ActionLine';
import { GroupsSelector } from '../../../../components/Selectors/GroupsSelector';
import { getGroupsByIdentifiers } from '../../../../repositories/GroupRepository';

const AddGroupsActionLine: React.FC<ActionLineProps> = ({
  lineNumber,
  handleDelete,
  currentCatalogLocale,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [unexistingGroupCodes, setUnexistingGroupCodes] = React.useState<
    GroupCode[]
  >([]);
  const {
    fieldFormName,
    typeFormName,
    itemsFormName,
    getItemsFormValue,
  } = useControlledFormInputAction<GroupCode[]>(lineNumber);

  React.useEffect(() => {
    // This method stores the unexisting groups at the loading of the line.
    // As there is no way to add unexisting groups, the only solution for the user to validate is
    // to manually remove groups
    const unexistingGroups: GroupCode[] = [];
    if (!getItemsFormValue() || getItemsFormValue().length === 0) {
      setUnexistingGroupCodes([]);
    } else {
      getGroupsByIdentifiers(getItemsFormValue(), router).then(groups => {
        getItemsFormValue().forEach(groupCode => {
          if (!groups[groupCode]) {
            unexistingGroups.push(groupCode);
          }
        });
        setUnexistingGroupCodes(unexistingGroups);
      });
    }
  }, []);

  const validateGroupCodes = (groupCodes: GroupCode[]) => {
    if (!groupCodes || !groupCodes.length) {
      return translate('pimee_catalog_rule.exceptions.required');
    }

    const unknownGroupCodes: GroupCode[] = groupCodes.filter(groupCode =>
      unexistingGroupCodes.includes(groupCode)
    );
    if (unknownGroupCodes.length) {
      return translate(
        'pimee_catalog_rule.exceptions.unknown_groups',
        {
          groupCodes: unknownGroupCodes.join(', '),
        },
        unknownGroupCodes.length
      );
    }

    return true;
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
        defaultValue='add'
      />
      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.add_groups.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.add_groups.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.add_groups.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.add_groups.subtitle'
              )}
            </ActionTitle>
            <AknActionFormContainer>
              <Controller
                as={GroupsSelector}
                id={`edit-rules-actions-${lineNumber}-items`}
                label={`${translate(
                  'pim_enrich.mass_edit.product.operation.add_to_group.field'
                )} ${translate('pim_common.required_label')}`}
                currentCatalogLocale={currentCatalogLocale}
                value={getItemsFormValue()}
                rules={{ validate: validateGroupCodes }}
                name={itemsFormName}
              />
            </AknActionFormContainer>
          </ActionLeftSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { AddGroupsActionLine };
