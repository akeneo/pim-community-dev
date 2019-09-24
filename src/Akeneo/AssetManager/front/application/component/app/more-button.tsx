import * as React from 'react';
import styled from 'styled-components';
import More from 'akeneoassetmanager/application/component/app/icon/more';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';
import {TransparentButton} from 'akeneopimenrichmentassetmanager/platform/component/common/button';
import Key from 'akeneoassetmanager/tools/key';

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

const Button: React.FunctionComponent<{title: string; onAction: () => void}> = ({children, title, onAction}) => {
  const Container = styled(TransparentButton)`
    margin: 0 10px;
  `;

  return (
    <Container
      title={title}
      tabIndex={0}
      onClick={() => onAction()}
      onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
        if (Key.Space === event.key) onAction();
      }}
    >
      {children}
    </Container>
  );
};

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
  const firstActionButton = React.useRef(null);

  React.useEffect(() => {
    if (isOpen && null !== firstActionButton.current) {
      (firstActionButton.current as any).focus();
    }
  }, [isOpen]);

  return (
    <Container>
      {isOpen ? (
        <Mask onClick={() => setOpen(false)}>{__('pim_asset_manager.asset_collection.dismiss_other_actions')}</Mask>
      ) : null}
      <Button title={__('pim_asset_manager.asset_collection.open_other_actions')} onAction={() => setOpen(true)}>
        <More />
      </Button>
      {isOpen ? (
        <Panel>
          <Title>{__('pim_asset_manager.asset_collection.other_actions')}</Title>
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
