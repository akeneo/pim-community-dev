import React, {useContext} from 'react';

const ModalContext = React.createContext(false);

const useInModal = () => useContext(ModalContext);

export {useInModal, ModalContext};
