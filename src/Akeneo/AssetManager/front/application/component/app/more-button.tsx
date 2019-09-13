import * as React from 'react';
import styled from 'styled-components';
import More from 'akeneoassetmanager/application/component/app/icon/more';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';

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
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  width: 100%;
  line-height: 34px;
  min-width: 120px;

  &:hover {
    cursor: pointer;
  }
`;

const Button = styled(More)`
  margin: 0 10px;

  &:hover {
    cursor: pointer;
  }
`;

const Title = styled.div`
  line-height: 44px;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.small};
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  text-transform: uppercase;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.purple100};
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
export const MoreButton = ({elements}: {elements: Element[]}) => {
  const [isOpen, setOpen] = React.useState(false);

  return (
    <Container>
      {isOpen ? (
        <Mask onClick={() => setOpen(false)}>{__('pim_asset_manager.asset_collection.dismiss_other_actions')}</Mask>
      ) : null}
      <Button title={__('pim_asset_manager.asset_collection.open_other_actions')} onClick={() => setOpen(true)} />
      {isOpen ? (
        <Panel>
          <Title>{__('pim_asset_manager.asset_collection.other_actions')}</Title>
          <ActionList>
            {elements.map((element: Element, index: number) => (
              // Not ideal to use the index here but the label is not unique
              <Action
                key={index}
                onClick={() => {
                  element.action();
                  setOpen(false);
                }}
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
