import React from 'react';
import styled from 'styled-components';

const Header = styled.header`
  font-weight: normal;
  margin-bottom: 20px;
  width: 100%;
`;
const Legend = styled.legend`
  font-weight: normal;
  margin-bottom: 20px;
  width: 100%;
`;

type Props = {title?: string};

const Subsection: React.FC<Props> = ({children, title}) => {
  return (
    <div className='AknSubsection'>
      {title && <Header className='AknSubsection-title'>{title}</Header>}
      {children}
    </div>
  );
};

const FormSubsection: React.FC<Props> = ({children, title}) => {
  return (
    <div className='AknSubsection'>
      <fieldset>
        {title && <Legend className='AknSubsection-title'>{title}</Legend>}
        {children}
      </fieldset>
    </div>
  );
};

export {Subsection, FormSubsection};
