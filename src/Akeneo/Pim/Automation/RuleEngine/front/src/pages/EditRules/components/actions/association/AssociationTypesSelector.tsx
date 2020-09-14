import React from 'react';
import {
  AssociationValue,
  GroupCode,
  ProductIdentifier,
  ProductModelCode,
} from '../../../../../models';
import { AssociationTarget, Target } from '../SetAssociationsActionLine';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from '../ActionLine';
import { Label } from '../../../../../components/Labels';
import { AssociationsGroupsSelector } from './AssociationsGroupsSelector';
import { AssociationsIdentifiersSelector } from './AssociationsIdentifiersSelector';
import {
  useTranslate,
  useUserCatalogLocale,
} from '../../../../../dependenciesTools/hooks';
import { AssociationTypeSwitcher } from './AssociationTypeSwitcher';
import { EmptySourceHelper } from '../style';

type Props = {
  value: AssociationValue;
  onChange: (value: AssociationValue) => void;
  hasError?: boolean;
  required: boolean;
};

const AssociationTypesSelector: React.FC<Props> = ({
  value,
  onChange,
  hasError,
  required,
}) => {
  const currentCatalogLocale = useUserCatalogLocale();
  const translate = useTranslate();

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
    const associationValuesArray: any = [];
    Object.keys(value || {}).forEach((associationTypeCode: string) => {
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

  if (typeof associationValues === 'undefined') {
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
    if (!currentAssociationTarget) {
      return Array.from(associationValues.keys())[0];
    }
    if (
      Array.from(associationValues.keys()).some(
        associationTarget =>
          associationTarget.associationTypeCode ===
            currentAssociationTarget.associationTypeCode &&
          associationTarget.target === currentAssociationTarget.target
      )
    ) {
      return currentAssociationTarget;
    }
    return Array.from(associationValues.keys())[0];
  };

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
    setCurrentAssociationTarget({ ...Array.from(associationValues.keys())[0] });
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

  const handleAddAssociationType = (associationTarget: AssociationTarget) => {
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
        <AssociationTypeSwitcher
          associationValues={associationValues}
          handleAssociationTargetDelete={handleAssociationTargetDelete}
          handleAddAssociationType={handleAddAssociationType}
          setCurrentAssociationTarget={setCurrentAssociationTarget}
          currentAssociationTarget={currentAssociationTargetOrDefault()}
          quantified={false}
          hasError={!!hasError}
        />
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
              )} ${required ? translate('pim_common.required_label') : ''}`}
            />
            {(currentAssociationTargetOrDefault() as AssociationTarget)
              .target === 'groups' && (
              <AssociationsGroupsSelector
                groupCodes={
                  (associationValues.get(
                    currentAssociationTargetOrDefault() as AssociationTarget
                  ) as GroupCode[]) || []
                }
                currentCatalogLocale={currentCatalogLocale}
                onChange={groupCodes =>
                  handleChange(
                    currentAssociationTargetOrDefault() as AssociationTarget,
                    groupCodes
                  )
                }
              />
            )}
            {(currentAssociationTargetOrDefault() as AssociationTarget)
              .target === 'products' && (
              <AssociationsIdentifiersSelector
                entityType='product'
                identifiers={
                  (associationValues.get(
                    currentAssociationTargetOrDefault() as AssociationTarget
                  ) as ProductIdentifier[]) || []
                }
                onChange={productIdentifiers =>
                  handleChange(
                    currentAssociationTargetOrDefault() as AssociationTarget,
                    productIdentifiers
                  )
                }
              />
            )}
            {(currentAssociationTargetOrDefault() as AssociationTarget)
              .target === 'product_models' && (
              <AssociationsIdentifiersSelector
                entityType='product_model'
                identifiers={
                  (associationValues.get(
                    currentAssociationTargetOrDefault() as AssociationTarget
                  ) as ProductModelCode[]) || []
                }
                onChange={productModelCodes =>
                  handleChange(
                    currentAssociationTargetOrDefault() as AssociationTarget,
                    productModelCodes
                  )
                }
              />
            )}
            {!required &&
              (
                associationValues.get(
                  currentAssociationTargetOrDefault() as AssociationTarget
                ) || []
              ).length === 0 && (
                <EmptySourceHelper>
                  {translate(
                    'pimee_catalog_rule.exceptions.empty_association_warning'
                  )}
                </EmptySourceHelper>
              )}
          </>
        )}
      </ActionRightSide>
    </ActionGrid>
  );
};

export { AssociationTypesSelector };
