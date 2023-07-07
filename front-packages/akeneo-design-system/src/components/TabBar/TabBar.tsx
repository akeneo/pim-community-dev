import React, {
  Children,
  cloneElement,
  HTMLAttributes,
  isValidElement,
  ReactElement,
  ReactNode,
  RefObject,
  useEffect,
  useRef,
  useState,
  KeyboardEvent,
} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, getColor, getFontSize} from '../../theme';
import {Dropdown} from '../Dropdown/Dropdown';
import {IconButton} from '../IconButton/IconButton';
import {MoreIcon} from '../../icons/MoreIcon';
import {useBooleanState} from '../../hooks';
import {Key, Override} from '../../shared';

const Container = styled.div<{sticky: number} & AkeneoThemedProps>`
  display: flex;
  align-items: center;
  border-bottom: 1px solid ${getColor('grey', 80)};
  background: ${getColor('white')};

  ${({sticky}) =>
    undefined !== sticky &&
    css`
      position: sticky;
      top: ${sticky}px;
      background-color: ${getColor('white')};
      z-index: 9;
    `}
`;

const TabBarContainer = styled.div`
  display: flex;
  gap: 10px;
  flex-grow: 1;
  height: 44px;
  flex-wrap: wrap;
  overflow: hidden;
  margin-bottom: -1px;
`;

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
    border-bottom: 3px solid ${getColor('brand', 100)};
  }
`;

const HiddenTabsDropdown = styled(Dropdown)<{isActive: boolean} & AkeneoThemedProps>`
  border-bottom: 3px solid ${({isActive}) => (isActive ? getColor('brand', 100) : 'transparent')};
  margin-bottom: -1px;
  height: 44px;
  box-sizing: border-box;
  align-items: center;

  &:hover {
    color: ${getColor('brand', 100)};
    border-bottom: 3px solid ${getColor('brand', 100)};
  }
`;

type TabProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
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

    /**
     * @private
     */
    parentRef?: RefObject<HTMLDivElement>;

    /**
     * @private
     */
    onVisibilityChange?: (newVisibility: boolean) => void;
  }
>;

const Tab = ({children, onClick, isActive, parentRef, onVisibilityChange, ...rest}: TabProps) => {
  const ref = useRef<HTMLDivElement>(null);

  const handleKeyDown = (event: KeyboardEvent<HTMLDivElement>) => {
    if (event.key === Key.Space || event.key === Key.Enter) {
      onClick?.();
    }
  };

  useEffect(() => {
    if (undefined === parentRef) {
      throw new Error('TabBar.Tab can not be used outside TabBar');
    }

    const tabElement = ref.current;
    const tabBarElement = parentRef.current;

    /* istanbul ignore next first render */
    if (null === tabElement) return;

    const options = {
      root: tabBarElement,
      rootMargin: '0px',
      threshold: 0,
    };

    const observer = new IntersectionObserver(entries => {
      const lastEntry = entries[entries.length - 1];

      onVisibilityChange?.(lastEntry.isIntersecting);
    }, options);

    observer.observe(tabElement);

    return () => {
      observer.unobserve(tabElement);
    };
  }, []);

  return (
    <TabContainer
      onKeyDown={handleKeyDown}
      onClick={onClick}
      ref={ref}
      tabIndex={0}
      role="tab"
      aria-selected={isActive}
      isActive={isActive}
      {...rest}
    >
      {children}
    </TabContainer>
  );
};

type TabBarProps = {
  /**
   * Title of the More button.
   */
  moreButtonTitle: string;

  /**
   * When set, defines the sticky top position of the Tab bar.
   */
  sticky?: number;

  /**
   * Tabs of the Tab bar.
   */
  children?: ReactNode;
} & HTMLAttributes<HTMLDivElement>;

/**
 * TabBar is used to move from one content to another within the same context.
 */
const TabBar = ({moreButtonTitle, children, ...rest}: TabBarProps) => {
  const ref = useRef<HTMLDivElement>(null);
  const [hiddenElements, setHiddenElements] = useState<string[]>([]);
  const [isOpen, open, close] = useBooleanState();

  const hiddenTabs: ReactElement<TabProps>[] = [];
  const decoratedChildren = Children.map(children, (child, index) => {
    if (!child) {
      return;
    }

    if (!isValidElement<TabProps>(child)) {
      throw new Error('TabBar only accepts TabBar.Tab as children');
    }

    const key = child.key !== null ? child.key : index;
    const isHidden = hiddenElements.includes(String(key));

    if (isHidden) {
      hiddenTabs.push(child);
    }

    return cloneElement(child, {
      parentRef: ref,
      tabIndex: isHidden ? -1 : 0,
      onVisibilityChange: (isVisible: boolean) => {
        setHiddenElements(previousHiddenElements =>
          isVisible
            ? previousHiddenElements.filter(hiddenElement => hiddenElement !== String(key))
            : [String(key), ...previousHiddenElements]
        );
      },
    });
  });

  const activeTabIsHidden = hiddenTabs.find(child => child.props.isActive) !== undefined;

  return (
    <Container {...rest}>
      <TabBarContainer ref={ref} role="tablist">
        {decoratedChildren}
      </TabBarContainer>
      {0 < hiddenTabs.length && (
        <HiddenTabsDropdown isActive={activeTabIsHidden}>
          <IconButton level="tertiary" ghost="borderless" icon={<MoreIcon />} title={moreButtonTitle} onClick={open} />
          {isOpen && (
            <Dropdown.Overlay verticalPosition="down" onClose={close}>
              <Dropdown.ItemCollection>
                {hiddenTabs.map((child, index) => {
                  const handleClick = () => {
                    close();
                    child.props.onClick?.();
                  };

                  return (
                    <Dropdown.Item key={index} onClick={handleClick} isActive={child.props.isActive}>
                      {child.props.children}
                    </Dropdown.Item>
                  );
                })}
              </Dropdown.ItemCollection>
            </Dropdown.Overlay>
          )}
        </HiddenTabsDropdown>
      )}
    </Container>
  );
};

TabBar.Tab = Tab;

export {TabBar};
