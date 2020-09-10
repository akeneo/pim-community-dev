import React from "react";
import { AddAssociationTypeButton } from "./AddAssociationTypeButton";
import { AssociationTarget } from "../SetAssociationsActionLine";
import {
  AssociationType,
  AssociationTypeCode,
} from "../../../../../models";
import { getQuantifiedAssociationTypes } from "../../../../../repositories/AssociationTypeRepository";
import { useBackboneRouter, useTranslate, useUserCatalogLocale } from "../../../../../dependenciesTools/hooks";

type Props = {
  associationValues: Map<AssociationTarget, any>;
  handleAssociationTargetDelete: (associationTarget: AssociationTarget) => void;
  handleAddAssociationType: (associationTarget: AssociationTarget) => void;
  setCurrentAssociationTarget: (associationTarget: AssociationTarget) => void;
  currentAssociationTarget?: AssociationTarget;
  hasError: boolean;
}

const AssociationTypeSwitcher: React.FC<Props> = ({
  associationValues,
  handleAssociationTargetDelete,
  handleAddAssociationType,
  setCurrentAssociationTarget,
  currentAssociationTarget,
  hasError,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const currentCatalogLocale = useUserCatalogLocale();

  const [associationTypes, setAssociationTypes] = React.useState<
    AssociationType[]
    >();

  React.useEffect(() => {
    getQuantifiedAssociationTypes(router).then(associationTypes => {
      setAssociationTypes(associationTypes);
    });
  }, []);

  if (typeof associationTypes === 'undefined') {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const getAssociationTypeLabel = (associationTypeCode: AssociationTypeCode) =>
    associationTypes?.find(
      associationType => associationType.code === associationTypeCode
    )?.labels?.[currentCatalogLocale] || `[${associationTypeCode}]`;

  const isCurrentAssociationTargetOrDefault: (
    associationTarget: AssociationTarget
  ) => boolean = ({ associationTypeCode, target }) => {
    return (
      !!currentAssociationTarget &&
      currentAssociationTarget.associationTypeCode === associationTypeCode &&
      currentAssociationTarget.target === target
    );
  };

  return <ul>
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
    <li
      className={`AknBadgedSelector-item${
        hasError ? ' select2-container-error' : ''
      }`}>
      <AddAssociationTypeButton
        onAddAssociationType={handleAddAssociationType}
        selectedTargets={Array.from(associationValues.keys())}
        data-testid={'association-types-selector'}
      />
    </li>
  </ul>
}

export { AssociationTypeSwitcher };
