import React, {
  Children,
  HTMLAttributes,
  isValidElement,
  ReactNode,
  RefObject,
  useEffect,
  useRef,
  useState,
} from 'react';
import styled from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Dropdown, IconButton} from '../../../lib';
import {MoreIcon} from '../../icons';
import {useBooleanState} from '../../hooks';

const Container = styled.div`
  display: flex;
  border-bottom: 1px solid ${getColor('grey', 80)};
`;

const TabBarContainer = styled.div`
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  flex-grow: 1;
  height: 44px;
  flex-wrap: wrap;
  overflow: hidden;
`;

type TabProps = {
  /**
   * Define if the tab is active.
   */
  isActive: boolean;
  /**
   * Function called when the user click on tab.
   */
  onClick?: () => void;
  /**
   * Content of the Tab.
   */
  children: ReactNode;

  parentRef: RefObject<HTMLDivElement>;

  onVisibilityChange: (newVisibility: boolean) => void;
};

const TabContainer = styled.div<TabProps & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  gap: 10px;
  padding-right: 40px;
  color: ${({isActive}) => (isActive ? getColor('brand', 100) : getColor('grey', 100))};
  border-bottom: 3px solid ${({isActive}) => (isActive ? getColor('brand', 100) : 'transparent')};
  font-size: ${getFontSize('big')};
  cursor: pointer;
  white-space: nowrap;
  height: 100%;
  box-sizing: border-box;

  &:hover {
    color: ${getColor('brand', 100)};
  }
`;

const Tab = ({children, isActive, parentRef, onVisibilityChange, ...rest}: TabProps) => {
  const ref = useRef<HTMLDivElement>();
  useEffect(() => {
    if (undefined === ref.current) return;
    const tabElement = ref.current;
    const tabBarElement = parentRef.current;

    const options = {
      root: tabBarElement,
      rootMargin: '0px',
      threshold: 1.0,
    };

    const observer = new IntersectionObserver(event => {
      onVisibilityChange(event[0].isIntersecting);
    }, options);

    observer.observe(tabElement);

    return () => {
      observer.unobserve(tabElement);
    };
  }, [onVisibilityChange, parentRef, ref]);

  return (
    <TabContainer ref={ref} tabIndex={0} role="tab" aria-selected={isActive} isActive={isActive} {...rest}>
      {children}
    </TabContainer>
  );
};

type TabBarProps = {
  /**
   * Tabs of the Tab bar.
   */
  children?: ReactNode;
} & HTMLAttributes<HTMLDivElement>;

/**
 * TabBar is used to move from one content to another within the same context.
 */
const TabBar = ({children, ...rest}: TabBarProps) => {
  const ref = useRef<HTMLDivElement>(null);
  const [hiddenElements, setHiddenElements] = useState<number[]>([]);
  const [isOpen, open, close] = useBooleanState();

  const decoratedChildren = Children.map(children, (child, index) => {
    if (!React.isValidElement<TabProps>(child)) return child;

    return React.cloneElement(child, {
      parentRef: ref,
      onVisibilityChange: (isVisible: boolean) => {
        setHiddenElements(previousHiddenElements =>
          isVisible
            ? previousHiddenElements.filter(hiddenElement => hiddenElement !== index)
            : [index, ...previousHiddenElements]
        );
      },
    });
  });

  return (
    <Container>
      <TabBarContainer ref={ref} role="tablist" {...rest}>
        {decoratedChildren}
      </TabBarContainer>
      {hiddenElements.length > 0 && (
        <Dropdown>
          <IconButton level="tertiary" ghost="borderless" icon={<MoreIcon />} title={'Open'} onClick={open} />
          {isOpen && (
            <Dropdown.Overlay verticalPosition="down" onClose={close}>
              <Dropdown.Header>
                <Dropdown.Title>Elements</Dropdown.Title>
              </Dropdown.Header>
              <Dropdown.ItemCollection>
                {decoratedChildren?.map((child, index) => {
                  if (!hiddenElements.includes(index) || !isValidElement<TabProps>(child)) return;

                  return <Dropdown.Item key={index}>{child.props.children}</Dropdown.Item>;
                })}
              </Dropdown.ItemCollection>
            </Dropdown.Overlay>
          )}
        </Dropdown>
      )}
    </Container>
  );
};

TabBar.Tab = Tab;

export {TabBar};
