import React from 'react';
import styled from 'styled-components';
import {VisuallyHidden} from 'reakit/VisuallyHidden';
import {Translate} from '../../dependenciesTools';

const CheckboxButton = styled.button`
  width: 24px;
  height: 24px;
  border-radius: 3px;
  border-color: rgb(216, 216, 216);
  padding: 0;
  background: none;
  border-width: 1px;
  border-style: solid;
`;

const CheckboxSelectedButton = styled(CheckboxButton)`
  background-color: ${({theme}) => theme.color.blue100};
  background-image: url(/bundles/pimui/images/icon-checkwhite.svg);
`;

type Props = {
  onClick: () => void;
  selected: boolean;
  translate: Translate;
};

const TreeCheckbox: React.FC<Props> = ({onClick, selected, translate}) => {
  if (selected) {
    return (
      <CheckboxSelectedButton type='button' onClick={onClick}>
        <VisuallyHidden>
          {translate('pimee_catalog_rule.item.selected')}
        </VisuallyHidden>
      </CheckboxSelectedButton>
    );
  }
  return (
    <CheckboxButton type='button' onClick={onClick}>
      <VisuallyHidden>
        {translate('pimee_catalog_rule.item.not_selected')}
      </VisuallyHidden>
    </CheckboxButton>
  );
};

export {TreeCheckbox};
