import React from 'react';
import styled from 'styled-components';
import { Translate } from '../../../../dependenciesTools';
import { RedGhostButton, SmallHelper } from '../../../../components';
import { TextBoxBlue } from '../TextBoxBlue';
import { VisuallyHidden } from 'reakit/VisuallyHidden';
import { AlertDialog } from '../../../../components/AlertDialog/AlertDialog';
import { createComponent } from 'reakit-system';
import { useDialogDisclosure, useDialogState } from 'reakit';

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

const DeleteButton = createComponent({
  as: RedGhostButton,
  useHook: useDialogDisclosure,
});

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
  const dialog = useDialogState();

  return (
    <fieldset>
      <StyledHeader className='AknSubsection-title'>
        <StyledHeaderLeft>
          <TextBoxBlue>
            {translate('pimee_catalog_rule.rule.action.then.label')}
          </TextBoxBlue>
          <StyledTitleHeader>{title}</StyledTitleHeader>
        </StyledHeaderLeft>
        <DeleteButton {...dialog} sizeMode='small'>
          {translate('pimee_catalog_rule.form.edit.actions.delete.label')}
        </DeleteButton>
        <AlertDialog
          dialog={dialog}
          onValidate={() => {
            handleDelete();
          }}
          cancelLabel={translate('pim_common.cancel')}
          confirmLabel={translate('pim_common.confirm')}
          label={translate('pimee_catalog_rule.form.edit.actions.delete.label')}
          description={translate(
            'pimee_catalog_rule.form.edit.actions.delete.description'
          )}
        />
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
