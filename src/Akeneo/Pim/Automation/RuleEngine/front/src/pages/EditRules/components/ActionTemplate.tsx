import React from 'react';
import styled from 'styled-components';
import { Translate } from '../../../dependenciesTools';
import { RedGhostButton, SmallHelper } from '../../../components';
import { TextBoxBlue } from './TextBoxBlue';
import { VisuallyHidden } from 'reakit/VisuallyHidden';

const StyledHeader = styled.header`
  font-weight: normal;
  margin-bottom: 0;
  width: 100%;
`;

const StyledHeaderLeft = styled.span`
  align-items: center;
  display: flex;
`;

const StyledTitleHeader = styled.span`
  padding-left: 8px;
`;

type Props = {
  translate: Translate;
  title: string;
  helper: string;
  legend: string;
  handleDelete: () => void;
};

const ActionTemplate: React.FC<Props> = ({
  legend,
  translate,
  title,
  helper,
  children,
  handleDelete,
}) => {
  return (
    <fieldset>
      <StyledHeader className='AknSubsection-title'>
        <StyledHeaderLeft>
          <TextBoxBlue>
            {translate('pimee_catalog_rule.rule.action.then.label')}
          </TextBoxBlue>
          <StyledTitleHeader>{title}</StyledTitleHeader>
        </StyledHeaderLeft>
        <RedGhostButton
          sizeMode='small'
          onClick={event => {
            event.preventDefault();
            handleDelete();
          }}>
          {translate('pimee_catalog_rule.form.edit.remove_action')}
        </RedGhostButton>
      </StyledHeader>
      <SmallHelper>{helper}</SmallHelper>
      <VisuallyHidden>
        <legend>{legend}</legend>
      </VisuallyHidden>
      {children}
    </fieldset>
  );
};

ActionTemplate.displayName = 'ActionTemplate';

export { ActionTemplate };
