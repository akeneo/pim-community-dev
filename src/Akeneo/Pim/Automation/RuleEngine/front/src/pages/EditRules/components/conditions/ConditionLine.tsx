import React from 'react';
import { useDialogState, DialogDisclosure } from 'reakit/Dialog';
import { AlertDialog } from '../../../../components/AlertDialog/AlertDialog';
import { PimConditionLine } from './PimConditionLine';
import { FallbackConditionLine } from './FallbackConditionLine';
import styled from 'styled-components';
import { Condition, Locale, LocaleCode } from '../../../../models';
import { IndexedScopes } from '../../../../repositories/ScopeRepository';
import { ConditionLineProps } from './ConditionLineProps';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { getConditionModule } from '../../../../models/conditions/ConditionModuleGuesser';

const DeleteButton = styled(DialogDisclosure)`
  border: none;
  background: none;
  cursor: pointer;
`;

const DeleteButtonContainer = styled.div`
  align-items: center;
`;

type Props = {
  lineNumber: number;
  locales: Locale[];
  scopes: IndexedScopes;
  currentCatalogLocale: LocaleCode;
  deleteCondition: (lineNumber: number) => void;
  condition: Condition;
};

const ConditionLine: React.FC<Props> = ({
  condition,
  lineNumber,
  locales,
  scopes,
  currentCatalogLocale,
  deleteCondition,
}) => {
  const translate = useTranslate();
  const dialog = useDialogState();
  const router = useBackboneRouter();
  const [Line, setLine] = React.useState<
    React.FC<ConditionLineProps & { condition: Condition }>
  >();
  React.useEffect(() => {
    getConditionModule(condition, router).then(module => setLine(() => module));
  }, []);

  if (!Line) {
    return (
      <div className='AknGrid-bodyCell'>
        <img
          src='/bundles/pimui/images//loader-V2.svg'
          alt={translate('pim_common.loading')}
        />
      </div>
    );
  }

  const isFallback =
    Line === PimConditionLine || Line === FallbackConditionLine;

  return (
    <div
      className={`AknGrid-bodyRow${
        isFallback ? ' AknGrid-bodyRow--highlight' : ''
      }`}>
      <Line
        condition={condition}
        lineNumber={lineNumber}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={currentCatalogLocale}
      />
      <DeleteButtonContainer className='AknGrid-bodyCell AknGrid-bodyCell--tight'>
        <DeleteButton {...dialog}>
          <img
            alt={translate('pimee_catalog_rule.form.edit.conditions.delete')}
            src='/bundles/akeneopimruleengine/assets/icons/icon-delete-grey100.svg'
          />
        </DeleteButton>
        <AlertDialog
          dialog={dialog}
          onValidate={() => {
            deleteCondition(lineNumber);
          }}
          label={translate(
            'pimee_catalog_rule.form.edit.conditions.delete.label'
          )}
          description={translate(
            'pimee_catalog_rule.form.edit.conditions.delete.description'
          )}
        />
      </DeleteButtonContainer>
    </div>
  );
};

export { ConditionLine };
