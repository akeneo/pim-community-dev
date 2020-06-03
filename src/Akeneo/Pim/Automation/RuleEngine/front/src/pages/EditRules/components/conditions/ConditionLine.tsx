import React from 'react';
import { useDialogState, DialogDisclosure } from 'reakit/Dialog';
import { AlertDialog } from '../../../../components/AlertDialog/AlertDialog';
import { PimConditionLine } from './PimConditionLine';
import { FallbackConditionLine } from './FallbackConditionLine';
import styled from 'styled-components';
import { Condition, Locale, LocaleCode } from '../../../../models';
import { Translate } from '../../../../dependenciesTools';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { ConditionLineProps } from './ConditionLineProps';

const DeleteButton = styled(DialogDisclosure)`
  border: none;
  background: none;
  cursor: pointer;
`;

type Props = {
  lineNumber: number;
  translate: Translate;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  deleteCondition: (lineNumber: number) => void;
  condition: Condition;
};

const ConditionLine: React.FC<Props> = ({
  translate,
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
  deleteCondition,
}) => {
  const dialog = useDialogState();

  const Line = condition.module as React.FC<
    ConditionLineProps & { condition: Condition }
  >;
  const isFallback =
    condition.module === PimConditionLine ||
    condition.module === FallbackConditionLine;

  return (
    <div
      className={`AknGrid-bodyRow${
        isFallback ? ' AknGrid-bodyRow--highlight' : ''
      }`}>
      <Line
        condition={condition}
        lineNumber={lineNumber}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
      />
      <div className='AknGrid-bodyCell AknGrid-bodyCell--tight'>
        <DeleteButton {...dialog}>
          <img
            alt={translate('pimee_catalog_rule.form.edit.conditions.delete')}
            src='/bundles/pimui/images/icon-delete-slategrey.svg'
          />
        </DeleteButton>
        <AlertDialog
          dialog={dialog}
          onValidate={() => {
            deleteCondition(lineNumber);
          }}
          cancelLabel={translate('pim_common.cancel')}
          confirmLabel={translate('pim_common.confirm')}
          label={translate(
            'pimee_catalog_rule.form.edit.conditions.delete.label'
          )}
          description={translate(
            'pimee_catalog_rule.form.edit.conditions.delete.description'
          )}
        />
      </div>
    </div>
  );
};

export { ConditionLine };
