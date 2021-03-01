import React, {ReactElement, ReactNode, useEffect, useRef} from 'react';
import {createPortal} from 'react-dom';
import styled from 'styled-components';
import {AkeneoThemedProps, CommonStyle, getColor, getFontSize} from '../../theme';
import {IconButton} from '../../components';
import {CloseIcon} from '../../icons';
import {IllustrationProps} from '../../illustrations/IllustrationProps';
import {useShortcut} from '../../hooks';
import {Key, Override} from '../../shared';

const ModalContainer = styled.div`
  ${CommonStyle}
  position: fixed;
  width: 100vw;
  height: 100vh;
  top: 0;
  left: 0;
  background-color: ${getColor('white')};
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  overflow: hidden;
  cursor: default;
  padding: 20px 80px;
  box-sizing: border-box;
`;

const ModalCloseButton = styled(IconButton)`
  position: fixed;
  top: 40px;
  left: 40px;
`;

const ModalContent = styled.div`
  display: grid;
  grid-template-columns: 1fr 2fr;
`;

const ModalChildren = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px 40px;
  min-width: 480px;
  border-left: 1px solid ${getColor('brand', 100)};
`;

const IconContainer = styled.div`
  display: flex;
  justify-content: flex-end;
  padding-right: 40px;
`;

//TODO extract to Typography RAC-331
const SectionTitle = styled.div<{size?: 'big' | 'small' | 'default'; color?: string} & AkeneoThemedProps>`
  height: 20px;
  color: ${({color}) => getColor(color ?? 'grey', 120)};
  font-size: ${({size}) => getFontSize(size ?? 'default')};
  text-transform: uppercase;
`;

//TODO extract to Typography RAC-331
const Title = styled.div`
  display: flex;
  align-items: center;
  height: 40px;
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  margin-bottom: 10px;
`;

const BottomButtons = styled.div`
  display: flex;
  gap: 10px;
  margin-top: 20px;
`;

const TopRightButtons = styled(BottomButtons)`
  position: fixed;
  top: 40px;
  right: 40px;
  margin: 0;
`;

type ModalProps = Override<
  React.HTMLAttributes<HTMLDivElement>,
  {
    /**
     * Illustration to display.
     */
    illustration?: ReactElement<IllustrationProps>;

    /**
     * Title of the close button.
     */
    closeTitle: string;

    /**
     * The content of the modal.
     */
    children?: ReactNode;

    /**
     * The handler to call when the Modal is closed.
     */
    onClose: () => void;
  }
>;

/**
 * The Modal Component is used to display a secondary window over the content.
 */
const Modal: React.FC<ModalProps> & {
  BottomButtons: typeof BottomButtons;
  TopRightButtons: typeof TopRightButtons;
  SectionTitle: typeof SectionTitle;
  Title: typeof Title;
} = ({onClose, illustration, closeTitle, children, ...rest}: ModalProps) => {
  const portalNode = document.createElement('div');
  portalNode.setAttribute('id', 'modal-root');
  const containerRef = useRef(portalNode);

  useShortcut(Key.Escape, onClose);

  useEffect(() => {
    document.body.appendChild(containerRef.current);

    return () => {
      document.body.removeChild(containerRef.current);
    };
  }, []);

  return createPortal(
    <ModalContainer role="dialog" {...rest}>
      <ModalCloseButton title={closeTitle} level="tertiary" ghost="borderless" icon={<CloseIcon />} onClick={onClose} />
      {undefined === illustration ? (
        children
      ) : (
        <ModalContent>
          <IconContainer>{React.cloneElement(illustration, {size: 220})}</IconContainer>
          <ModalChildren>{children}</ModalChildren>
        </ModalContent>
      )}
    </ModalContainer>,
    containerRef.current
  );
};

Modal.BottomButtons = BottomButtons;
Modal.TopRightButtons = TopRightButtons;
Modal.Title = Title;
Modal.SectionTitle = SectionTitle;

export {Modal};
