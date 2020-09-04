import React from 'react';
import { Controller } from 'react-hook-form';
import {
  AssociationValue,
  ProductIdentifier,
  SetAssociationsAction,
} from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { useControlledFormInputAction } from '../../hooks';
import { ActionTemplate } from './ActionTemplate';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../dependenciesTools/hooks';
import {
  AssociationType,
  AssociationTypeCode,
  GroupCode,
} from '../../../../models';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import { Label } from '../../../../components/Labels';
import { getAllAssociationTypes } from '../../../../repositories/AssociationTypeRepository';
import { AssociationsGroupsSelector } from './association/AssociationsGroupsSelector';

type Props = {
  action?: SetAssociationsAction;
} & ActionLineProps;

type Target = 'products' | 'product_models' | 'groups';

type AssociationTarget = {
  associationTypeCode: AssociationTypeCode;
  target: Target;
};

const SetAssociationsActionLine: React.FC<Props> = ({
  lineNumber,
  handleDelete,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const currentCatalogLocale = useUserCatalogLocale();

  const {
    fieldFormName,
    typeFormName,
    getValueFormValue,
    setValueFormValue,
  } = useControlledFormInputAction<AssociationValue>(lineNumber);

  const [associationTypes, setAssociationTypes] = React.useState<
    AssociationType[]
  >();
  const [associationValues, setAssociationValues] = React.useState<
    Map<AssociationTarget, ProductIdentifier[] | GroupCode[]>
  >();
  const [
    currentAssociationTarget,
    setCurrentAssociationTarget,
  ] = React.useState<AssociationTarget>();

  React.useEffect(() => {
    getAllAssociationTypes(router).then(associationTypes => {
      setAssociationTypes(associationTypes);
    });
  }, []);

  React.useEffect(() => {
    const value = getValueFormValue() ?? {};
    const associationValuesArray: any = [];
    Object.keys(value).forEach((associationTypeCode: string) => {
      (['products', 'product_models', 'groups'] as Target[]).forEach(target => {
        if (
          Object.prototype.hasOwnProperty.call(
            value[associationTypeCode],
            target
          )
        ) {
          associationValuesArray.push([
            { associationTypeCode, target },
            value[associationTypeCode][target],
          ]);
        }
      });
    });
    const associationValues = new Map<
      AssociationTarget,
      ProductIdentifier[] | GroupCode[]
    >(associationValuesArray);
    setAssociationValues(associationValues);
    setCurrentAssociationTarget(Array.from(associationValues.keys())[0]);
  }, []);

  if (!associationValues || !associationTypes) {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const isCurrentAssociationTargetOrDefault: (
    associationTarget: AssociationTarget
  ) => boolean = ({ associationTypeCode, target }) => {
    return (
      !!currentAssociationTarget &&
      currentAssociationTarget.associationTypeCode === associationTypeCode &&
      currentAssociationTarget.target === target
    );
  };

  const getAssociationTypeLabel = (associationTypeCode: AssociationTypeCode) =>
    associationTypes?.find(
      associationType => associationType.code === associationTypeCode
    )?.labels?.[currentCatalogLocale] || `[${associationTypeCode}]`;

  const formatAssociationValues = () => {
    return Array.from(associationValues.entries()).reduce(
      (result, [associationTarget, value]) => {
        if (!result[associationTarget.associationTypeCode]) {
          result[associationTarget.associationTypeCode] = {};
        }
        result[associationTarget.associationTypeCode][
          associationTarget.target
        ] = value;
        return result;
      },
      {} as AssociationValue
    );
  };

  const handleAssociationTargetDelete = (associationTarget: AssociationTarget) => {
    associationValues.delete(associationTarget);
    setAssociationValues(associationValues);
    setValueFormValue(formatAssociationValues());
  }

  const onGroupsChange = (
    associationTarget: AssociationTarget,
    groupCodes: GroupCode[]
  ) => {
    associationValues.set(associationTarget, groupCodes);
    setAssociationValues(new Map(associationValues));
    setValueFormValue(formatAssociationValues());
  };

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='associations'
      />
      <Controller
        as={<input type='hidden' />}
        name={typeFormName}
        defaultValue='set'
      />

      <ActionTemplate
        title={translate(
          'pimee_catalog_rule.form.edit.actions.set_associations.title'
        )}
        helper={translate(
          'pimee_catalog_rule.form.edit.actions.set_associations.helper'
        )}
        legend={translate(
          'pimee_catalog_rule.form.edit.actions.set_associations.helper'
        )}
        handleDelete={handleDelete}
        lineNumber={lineNumber}>
        <ActionGrid>
          <ActionLeftSide>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.set_associations.select_association_type'
              )}
            </ActionTitle>
            <Label
              className='AknFieldContainer-label control-label'
              label={`${translate(
                'pimee_catalog_rule.form.edit.actions.set_associations.association_types'
              )} ${translate('pim_common.required_label')}`}
            />
            <ul>
              {Array.from(associationValues.entries()).map(
                ([associationTarget, value]) => {
                  return (
                    <li
                      key={`${associationTarget.associationTypeCode}-${associationTarget.target}`}
                      className={'AknBadgedSelector-item'}>
                      <button
                        data-testid={`association-type-selector-${associationTarget.associationTypeCode}-${associationTarget.target}`}
                        className={`AknTextField AknBadgedSelector${
                          isCurrentAssociationTargetOrDefault(associationTarget)
                            ? ' AknBadgedSelector--selected'
                            : ''
                        }`}
                        onClick={e => {
                          e.preventDefault();
                          setCurrentAssociationTarget(associationTarget);
                        }}>
                        {getAssociationTypeLabel(
                          associationTarget.associationTypeCode
                        )}
                        <span className='AknBadgedSelector-helper'>
                          {translate(
                            `pimee_catalog_rule.form.edit.actions.set_associations.counts.${associationTarget.target}`,
                            { count: value.length },
                            value.length
                          )}
                        </span>
                        <span
                          className='AknBadgedSelector-delete'
                          tabIndex={0}
                          onClick={() => handleAssociationTargetDelete(associationTarget)}
                          role='button'
                        />
                      </button>
                    </li>
                  );
                }
              )}
            </ul>
          </ActionLeftSide>
          <ActionRightSide>
            {currentAssociationTarget && (
              <>
                <ActionTitle>
                  {translate(
                    `pimee_catalog_rule.form.edit.actions.set_associations.select_title.${currentAssociationTarget.target}`
                  )}
                </ActionTitle>
                <Label
                  className='AknFieldContainer-label control-label'
                  label={`${translate(
                    `pimee_catalog_rule.form.edit.actions.set_associations.select.${currentAssociationTarget?.target}`
                  )} ${translate('pim_common.required_label')}`}
                />
                {currentAssociationTarget.target === 'groups' ? (
                  <AssociationsGroupsSelector
                    groupCodes={
                      associationValues.get(
                        currentAssociationTarget
                      ) as GroupCode[] || []
                    }
                    currentCatalogLocale={currentCatalogLocale}
                    onChange={groupCodes =>
                      onGroupsChange(currentAssociationTarget, groupCodes)
                    }
                  />
                ) : (
                  <>
                    {JSON.stringify(
                      associationValues.get(currentAssociationTarget)
                    )}
                  </>
                )}
              </>
            )}
          </ActionRightSide>
        </ActionGrid>
      </ActionTemplate>
    </>
  );
};

export { SetAssociationsActionLine };
