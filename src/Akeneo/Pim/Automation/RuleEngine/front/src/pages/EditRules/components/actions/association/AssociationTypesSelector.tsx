import React from 'react';
import {
  AssociationType,
  AssociationTypeCode,
  GroupCode,
  ProductIdentifier,
  ProductModelCode,
} from '../../../../../models';
import { getAssociationTypesFromQuantified } from '../../../../../repositories/AssociationTypeRepository';
import { AssociationValue } from '../../../../../models/actions';
import { AssociationTarget, Target } from '../SetAssociationsActionLine';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from '../ActionLine';
import { Label } from '../../../../../components/Labels';
import { AddAssociationTypeButton } from './AddAssociationTypeButton';
import { AssociationsGroupsSelector } from './AssociationsGroupsSelector';
import { AssociationsIdentifiersSelector } from './AssociationsIdentifiersSelector';
import {
  useBackboneRouter,
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';

type Props = {
  value: AssociationValue;
  onChange: (value: AssociationValue) => void;
};

const AssociationTypesSelector: React.FC<Props> = ({ value, onChange }) => {
  const router = useBackboneRouter();
  const currentCatalogLocale = useUserCatalogLocale();
  const translate = useTranslate();

  const [associationTypes, setAssociationTypes] = React.useState<
    AssociationType[]
  >();
  const [associationValues, setAssociationValues] = React.useState<
    Map<
      AssociationTarget,
      ProductIdentifier[] | GroupCode[] | ProductModelCode[]
    >
  >();
  const [
    currentAssociationTarget,
    setCurrentAssociationTarget,
  ] = React.useState<AssociationTarget>();

  React.useEffect(() => {
    getAssociationTypesFromQuantified(router, false).then(associationTypes => {
      setAssociationTypes(associationTypes);
    });
  }, []);

  React.useEffect(() => {
    const associationValuesArray: any = [];
    Object.keys(value).forEach((associationTypeCode: string) => {
      (['products', 'product_models', 'groups'] as Target[]).forEach(target => {
        if (typeof value[associationTypeCode][target] !== 'undefined') {
          associationValuesArray.push([
            { associationTypeCode, target },
            value[associationTypeCode][target],
          ]);
        }
      });
    });
    const associationValues = new Map<
      AssociationTarget,
      ProductIdentifier[] | GroupCode[] | ProductModelCode[]
    >(associationValuesArray);
    setAssociationValues(associationValues);
    setCurrentAssociationTarget(Array.from(associationValues.keys())[0]);
  }, []);

  if (
    typeof associationValues === 'undefined' ||
    typeof associationTypes === 'undefined'
  ) {
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

  const handleAssociationTargetDelete = (
    associationTarget: AssociationTarget
  ) => {
    associationValues.delete(associationTarget);
    setAssociationValues(new Map(associationValues));
    setCurrentAssociationTarget(Array.from(associationValues.keys())[0]);
    onChange(formatAssociationValues());
  };

  const handleChange = (
    associationTarget: AssociationTarget,
    value: GroupCode[] | ProductModelCode[] | ProductIdentifier[]
  ) => {
    associationValues.set(associationTarget, value);
    setAssociationValues(new Map(associationValues));
    onChange(formatAssociationValues());
  };

  const handleAddAssociationType = (
    associationTypeCode: AssociationTypeCode,
    target: Target
  ) => {
    const associationTarget = { associationTypeCode, target };
    associationValues.set(associationTarget, []);
    setAssociationValues(new Map(associationValues));
    setCurrentAssociationTarget(associationTarget);
    onChange(formatAssociationValues());
  };

  return (
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
                      data-testid={`delete-association-type-button-${associationTarget.associationTypeCode}-${associationTarget.target}`}
                      onClick={() =>
                        handleAssociationTargetDelete(associationTarget)
                      }
                      role='button'
                    />
                  </button>
                </li>
              );
            }
          )}
          <li className={'AknBadgedSelector-item'}>
            <AddAssociationTypeButton
              onAddAssociationType={handleAddAssociationType}
              selectedTargets={Array.from(associationValues.keys())}
              data-testid={'association-types-selector'}
            />
          </li>
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
            {currentAssociationTarget.target === 'groups' && (
              <AssociationsGroupsSelector
                groupCodes={
                  (associationValues.get(
                    currentAssociationTarget
                  ) as GroupCode[]) || []
                }
                currentCatalogLocale={currentCatalogLocale}
                onChange={groupCodes =>
                  handleChange(currentAssociationTarget, groupCodes)
                }
              />
            )}
            {currentAssociationTarget.target === 'products' && (
              <AssociationsIdentifiersSelector
                entityType='product'
                identifiers={
                  (associationValues.get(
                    currentAssociationTarget
                  ) as ProductIdentifier[]) || []
                }
                onChange={productIdentifiers =>
                  handleChange(currentAssociationTarget, productIdentifiers)
                }
              />
            )}
            {currentAssociationTarget.target === 'product_models' && (
              <AssociationsIdentifiersSelector
                entityType='product_model'
                identifiers={
                  (associationValues.get(
                    currentAssociationTarget
                  ) as ProductModelCode[]) || []
                }
                onChange={productModelCodes =>
                  handleChange(currentAssociationTarget, productModelCodes)
                }
              />
            )}
          </>
        )}
      </ActionRightSide>
    </ActionGrid>
  );
};

export { AssociationTypesSelector };
