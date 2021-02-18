import React from 'react';
import styled from 'styled-components';
import {MoreIcon, getColor, getFontSize, Key, IconButton} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Mask = styled.button`
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 803;
  opacity: 0;
`;

const Panel = styled.ul`
  padding: 10px 20px;
  position: absolute;
  top: 0;
  right: 0;
  background-color: white;
  box-shadow: 1px 2px 8px rgba(0, 0, 0, 0.15);
  z-index: 801;
  min-width: 100%;
  max-height: 70vh;
  overflow: auto;
  font-style: normal;
  z-index: 804;
`;

const Action = styled.li`
  white-space: nowrap;
  text-align: left;
  font-size: ${getFontSize('default')};
  color: ${getColor('grey', 120)};
  width: 100%;
  line-height: 34px;
  min-width: 120px;

  &:hover {
    cursor: pointer;
  }
`;

const Title = styled.div`
  line-height: 44px;
  font-size: ${getFontSize('small')};
  color: ${getColor('brand', 100)};
  text-transform: uppercase;
  border-bottom: 1px solid ${getColor('brand', 100)};
  margin-bottom: 10px;
`;
const ActionList = styled.div``;

const Container = styled.div`
  position: relative;
  display: flex;
`;

type Element = {
  label: string;
  action: () => void;
};

//TODO use DSM Dropdown
export const MoreButton = ({elements}: {elements: Element[]}) => {
  const [isOpen, setOpen] = React.useState(false);
  const firstActionButton = React.useRef(null);
  const translate = useTranslate();

  React.useEffect(() => {
    if (isOpen && null !== firstActionButton.current) {
      (firstActionButton.current as any).focus();
    }
  }, [isOpen]);

  return (
    <Container>
      {isOpen ? (
        <Mask onClick={() => setOpen(false)}>
          {translate('pim_asset_manager.asset_collection.dismiss_other_actions')}
        </Mask>
      ) : null}
      <IconButton
        icon={<MoreIcon />}
        ghost="borderless"
        level="tertiary"
        title={translate('pim_asset_manager.asset_collection.open_other_actions')}
        onClick={() => setOpen(true)}
      />
      {isOpen ? (
        <Panel>
          <Title>{translate('pim_asset_manager.asset_collection.other_actions')}</Title>
          <ActionList>
            {elements.map((element: Element, index: number) => (
              // Not ideal to use the index here but the label is not unique
              <Action
                key={index}
                tabIndex={0}
                ref={0 === index ? firstActionButton : null}
                onKeyPress={(event: React.KeyboardEvent<HTMLLIElement>) => {
                  if (Key.Space === event.key) {
                    element.action();
                    setOpen(false);
                  }
                }}
                onClick={() => {
                  element.action();
                  setOpen(false);
                }}
                title={element.label}
              >
                {element.label}
              </Action>
            ))}
          </ActionList>
        </Panel>
      ) : null}
    </Container>
  );
};
