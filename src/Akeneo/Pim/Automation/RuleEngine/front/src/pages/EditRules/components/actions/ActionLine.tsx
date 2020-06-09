import React from 'react';
import { Action } from '../../../../models/Action';
import { ActionLineProps } from './ActionLineProps';
import styled from 'styled-components';
import { getActionModule } from '../../../../models/actions/ActionModuleGuesser';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';

const ActionTitle = styled.div`
  color: ${({ theme }): string => theme.color.purple100};
  font-size: 20px;
  line-height: 40px;
  margin-bottom: 15px;
`;

const ActionGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(2, minmax(140px, 1fr));
`;
const ActionLeftSide = styled.div`
  grid-column: 1;
  vertical-align: top;
  padding-right: 20px;
`;

const ActionRightSide = styled(ActionLeftSide)`
  border-left: 1px solid ${({ theme }): string => theme.color.purple100};
  grid-column: 2;
  vertical-align: top;
  padding-left: 20px;
`;

const ActionLine: React.FC<{ action: Action } & ActionLineProps> = ({
  action,
  lineNumber,
  handleDelete,
  currentCatalogLocale,
  locales,
  scopes,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const [Line, setLine] = React.useState<
    React.FC<ActionLineProps & { action: Action }>
  >();
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
      action={action}
      lineNumber={lineNumber}
      handleDelete={handleDelete}
      currentCatalogLocale={currentCatalogLocale}
      locales={locales}
      scopes={scopes}
    />
  );
};

export { ActionLine, ActionTitle, ActionGrid, ActionLeftSide, ActionRightSide };
