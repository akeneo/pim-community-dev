import React, {ReactNode, PropsWithChildren, useEffect} from 'react';

interface Props {
    subTitle: ReactNode;
    title: ReactNode;
    description: ReactNode;
    onCancel: () => void;
}

export const Modal = ({subTitle, title, description, children, onCancel}: PropsWithChildren<Props>) => {
    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => 'Escape' === event.code && onCancel();
        document.addEventListener('keydown', handleKeyDown, true);

        return () => document.removeEventListener('keydown', handleKeyDown, true);
    }, [onCancel]);

    return (
        <>
            <div className='AknFullPage'>
                <div className='AknFullPage-content AknFullPage-content--withIllustration'>
                    <div>
                        <div className='AknFullPage-image AknFullPage-illustration AknFullPage-illustration--api' />
                    </div>

                    <div>
                        <div className='AknFullPage-titleContainer'>
                            <div className='AknFullPage-subTitle'>{subTitle}</div>
                            <div className='AknFullPage-title'>{title}</div>
                            {description}
                        </div>

                        {children}
                    </div>
                </div>
            </div>

            <div className='AknFullPage-cancel' onClick={onCancel}></div>
        </>
    );
};
