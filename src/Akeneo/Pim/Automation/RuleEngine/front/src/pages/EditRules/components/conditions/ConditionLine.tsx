import React from 'react';
import { useDialogState, DialogDisclosure } from 'reakit/Dialog';
import { AlertDialog } from '../../../../components/AlertDialog/AlertDialog';
import { PimConditionLine } from './PimConditionLine';
import { FallbackConditionLine } from './FallbackConditionLine';
import styled from 'styled-components';
import { ConditionLineProps } from '../../ConditionLineProps';

const DeleteButton = styled(DialogDisclosure)`
  border: none;
  background: none;
  cursor: pointer;
`;

const ConditionLine: React.FC<ConditionLineProps & {
  deleteCondition: (lineNumber: number) => void;
}> = ({
  translate,
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
  deleteCondition,
  router,
}) => {
  const dialog = useDialogState();

  const Line = condition.module;
  const isFallback =
    condition.module === PimConditionLine ||
    condition.module === FallbackConditionLine;

  return (
    <div
      className={`AknGrid-bodyRow${
        isFallback ? ' AknGrid-bodyRow--highlight' : ''
      }`}>
      <div className='AknGrid-bodyCell'>
        <Line
          condition={condition}
          lineNumber={lineNumber}
          translate={translate}
          locales={locales}
          scopes={scopes}
          currentCatalogLocale={currentCatalogLocale}
          router={router}
        />
      </div>
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
