import React from 'react';
import {Action} from '../../../../models/Action';
import {ActionLineProps} from './ActionLineProps';
import styled from 'styled-components';
import {getActionModule} from '../../../../models/actions/ActionModuleGuesser';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';

const AknActionFormContainer = styled.div`
  width: 300px;
`;

const ActionTitle = styled.div`
  color: ${({theme}): string => theme.color.purple100};
  font-size: 17px;
  line-height: 30px;
  margin-bottom: 15px;
`;

const ActionGrid = styled.div`
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-gap: 40px;
`;

const ActionGridItem: React.FC = ({children}) => (
  <div className='ActionGridItem'>{children}</div>
);

const ActionLeftSide = ActionGridItem;
const ActionRightSide = ActionGridItem;

const ActionLine: React.FC<{action: Action} & ActionLineProps> = ({
  action,
  lineNumber,
  handleDelete,
  currentCatalogLocale,
  locales,
  uiLocales,
  scopes,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const [Line, setLine] = React.useState<React.FC<ActionLineProps>>();
  React.useEffect(() => {
    getActionModule(action, router).then(module => setLine(() => module));
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

  return (
    <Line
      lineNumber={lineNumber}
      handleDelete={handleDelete}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      uiLocales={uiLocales}
      scopes={scopes}
    />
  );
};

export {
  ActionLine,
  ActionTitle,
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  AknActionFormContainer,
};
