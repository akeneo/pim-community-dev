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
import { GroupCode, AssociationType } from '../../../../models';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import { Label } from '../../../../components/Labels';
import { getAllAssociationTypes } from '../../../../repositories/AssociationTypeRepository';

type Props = {
  action?: SetAssociationsAction;
} & ActionLineProps;

type Target = 'products' | 'product_models' | 'groups';

type AssociationTarget = {
  associationType: AssociationType;
  target: Target;
};

type AssociationValues = AssociationTarget & {
  values: ProductIdentifier[] | GroupCode[];
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
  } = useControlledFormInputAction<AssociationValue>(lineNumber); // TODO TYPE

  const [associationTypes, setAssociationTypes] = React.useState<
    AssociationType[]
  >();
  const [associationValues, setAssociationValues] = React.useState<
    AssociationValues[]
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
    if (!associationTypes) {
      return;
    }
    const value = getValueFormValue() ?? {};
    const associationValues: AssociationValues[] = [];
    Object.keys(value).forEach((associationTypeCode: string) => {
      const associationType = associationTypes.find(
        (associationType: AssociationType) =>
          associationType.code === associationTypeCode
      );
      if (associationType) {
        (['products', 'product_models', 'groups'] as Target[]).forEach(
          target => {
            if (
              value[associationTypeCode][target] &&
              Array.isArray(value[associationTypeCode][target])
            ) {
              const values = value[associationTypeCode][target] as
                | ProductIdentifier[]
                | GroupCode[];
              associationValues.push({
                associationType,
                target,
                values,
              });
            }
          }
        );
      } else {
        // TODO RUL-442 Manage unexisting association types
      }
    });
    setAssociationValues(associationValues);
  }, [JSON.stringify(associationTypes)]);

  if (!associationValues) {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const currentAssociationTargetOrDefault: () =>
    | AssociationTarget
    | undefined = () => {
    if (currentAssociationTarget) {
      return currentAssociationTarget;
    }
    if (associationValues.length) {
      return {
        associationType: associationValues[0].associationType,
        target: associationValues[0].target,
      };
    }
    return undefined;
  };

  const getMatchingAssociation = (
    associationType: AssociationType,
    target: Target
  ) => {
    return associationValues.find(associationValue => {
      return (
        associationValue.associationType.code === associationType.code &&
        associationValue.target === target
      );
    });
  };

  const getCount = (associationType: AssociationType, target: Target) => {
    const matching = getMatchingAssociation(associationType, target);
    if (matching) {
      return matching.values.length;
    }

    return 0;
  };

  const isCurrentAssociationTargetOrDefault = (
    associationType: AssociationType,
    target: Target
  ) => {
    return (
      currentAssociationTargetOrDefault() &&
      (currentAssociationTargetOrDefault() as AssociationTarget).associationType
        .code === associationType.code &&
      (currentAssociationTargetOrDefault() as AssociationTarget).target ===
        target
    );
  };

  return (
    <>
      <Controller
        as={<input type='hidden' />}
        name={fieldFormName}
        defaultValue='categories'
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
              {associationValues.map(associationValue => {
                return (
                  <li
                    key={`${associationValue.associationType.code}-${associationValue.target}`}
                    className={'AknCategoryTreeSelector-item'}>
                    <button
                      className={`AknTextField AknCategoryTreeSelector${
                        isCurrentAssociationTargetOrDefault(
                          associationValue.associationType,
                          associationValue.target
                        )
                          ? ' AknCategoryTreeSelector--selected'
                          : ''
                      }`}
                      onClick={e => {
                        e.preventDefault();
                        setCurrentAssociationTarget({
                          associationType: associationValue.associationType,
                          target: associationValue.target,
                        });
                      }}>
                      {associationValue.associationType.labels[
                        currentCatalogLocale
                      ] || `[${associationValue.associationType.code}]`}
                      <span className='AknCategoryTreeSelector-helper'>
                        {translate(
                          `pimee_catalog_rule.form.edit.actions.set_associations.counts.${associationValue.target}`,
                          {
                            count: getCount(
                              associationValue.associationType,
                              associationValue.target
                            ),
                          },
                          getCount(
                            associationValue.associationType,
                            associationValue.target
                          )
                        )}
                      </span>
                    </button>
                  </li>
                );
              })}
            </ul>
          </ActionLeftSide>
          <ActionRightSide>
            {currentAssociationTargetOrDefault() && (
              <>
                <ActionTitle>
                  {translate(
                    `pimee_catalog_rule.form.edit.actions.set_associations.select_title.${
                      currentAssociationTargetOrDefault()?.target
                    }`
                  )}
                </ActionTitle>
                <Label
                  className='AknFieldContainer-label control-label'
                  label={`${translate(
                    `pimee_catalog_rule.form.edit.actions.set_associations.select.${
                      currentAssociationTargetOrDefault()?.target
                    }`
                  )} ${translate('pim_common.required_label')}`}
                />
                {JSON.stringify(
                  getMatchingAssociation(
                    (currentAssociationTargetOrDefault() as AssociationTarget)
                      .associationType,
                    (currentAssociationTargetOrDefault() as AssociationTarget)
                      .target
                  )?.values
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
