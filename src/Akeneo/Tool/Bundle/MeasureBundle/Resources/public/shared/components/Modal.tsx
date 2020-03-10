import React, {PropsWithChildren} from 'react';
import styled, {css} from 'styled-components';
// import {Button, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {ThemedProps} from 'akeneomeasure/shared/theme';
import {ReactElement} from "react";
// import {AssetFamily} from 'akeneoassetmanager/application/component/app/illustration/asset-family';
// import {useShortcut} from 'akeneoassetmanager/application/hooks/input';
// import Key from 'akeneoassetmanager/tools/key';

export const Modal = styled.div`
  background: white;
  border-radius: 0;
  border: none;
  display: flex;
  flex-direction: column;
  height: 100%;
  left: 0;
  overflow-x: auto;
  padding: 40px;
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 1030;
`;

export const ModalHeader = styled.div<{sticky?: boolean}>`
  background: ${(props: ThemedProps<void>) => props.theme.color.white};
  position: relative;
  z-index: 2;

  ${props => props.sticky && css`
    position: sticky;
    top: 0px;

    :before {
      content: '';
      background: ${(props: ThemedProps<void>) => props.theme.color.white};
      display: block;
      position: absolute;
      height: 40px;
      top: -40px;
      width: 100%;
    }
  `}
`;

const ModalBodyWithColumns = styled.div`
  display: flex;
  flex-direction: row;
  flex-grow: 1;
  margin: 100px 0;
`;

const ModalBodyColumnLeft = styled.div`
  margin: 0 30px 0 0;
  text-align: right;
  width: 480px;
`;

const ModalBodyColumnRight = styled.div`
  border-left: 1px solid ${(props: ThemedProps<void>) => props.theme.color.purple100};
  padding: 0 0 0 30px;
`;

type ModalWithIllustationProps = {
  illustration: ReactElement;
}

export const ModalBodyWithIllustration = ({
  illustration,
  children,
}: PropsWithChildren<ModalWithIllustationProps>) => {
  return (
    <ModalBodyWithColumns>
      <ModalBodyColumnLeft>{illustration}</ModalBodyColumnLeft>
      <ModalBodyColumnRight>{children}</ModalBodyColumnRight>
    </ModalBodyWithColumns>
  )
};

//
// export const ScrollableModal = styled(Modal)`
//   overflow: hidden;
//   padding-bottom: 0;
// `;
//
// export const ConfirmButton = styled(Button)`
//   position: absolute;
//   top: 0;
//   right: 0;
// `;
//
// export const Title = styled.div`
//   color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
//   font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
//   line-height: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
//   margin-bottom: 23px;
//   text-align: center;
//   width: 100%;
// `;
//
// export const SubTitle = styled.div`
//   color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
//   font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
//   margin-bottom: 4px;
//   text-align: center;
//   text-transform: uppercase;
//   width: 100%;
// `;
//
// export const Header = styled.div`
//   position: relative;
// `;

// type ConfirmModalProps = {
//   titleContent: string;
//   content: string;
//   cancelButtonText: string;
//   confirmButtonText: string;
//   onCancel: () => void;
//   onConfirm: () => void;
// };
//
// export const ConfirmModal = ({
//   titleContent,
//   content,
//   cancelButtonText,
//   confirmButtonText,
//   onCancel,
//   onConfirm,
// }: ConfirmModalProps) => {
//   useShortcut(Key.Escape, onCancel);
//
//   return (
//     <Modal>
//       <div className="AknFullPage modal-body">
//         <div className="AknFullPage-content AknFullPage-content--withIllustration">
//           <div>
//             <AssetFamily className="AknFullPage-image" />
//           </div>
//           <div>
//             <div className="AknFullPage-titleContainer">
//               <div className="AknFullPage-title">{titleContent}</div>
//               <div className="AknFullPage-description">{content}</div>
//             </div>
//             <ButtonContainer>
//               <Button color="grey" onClick={onCancel}>{cancelButtonText}</Button>
//               <Button color="blue" onClick={onConfirm}>{confirmButtonText}</Button>
//             </ButtonContainer>
//           </div>
//         </div>
//       </div>
//       <div className="AknFullPage-cancel cancel" onClick={onCancel}/>
//     </Modal>
//   )
// };
