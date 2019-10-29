import * as React from 'react';
import styled from 'styled-components';
import {LegacyContext} from '../legacy-context';

interface Props {
    viewName: string;
    className: string;
}

const StyledPimView = styled.div<{rendered: boolean}>`
    visibility: ${({rendered}) => (rendered ? 'visible' : 'hidden')};
    opacity: ${({rendered}) => (rendered ? '1' : '0')};
    transition: opacity 0.5s linear;
`;

export const PimView = ({viewName, className}: Props) => {
    const el = React.useRef<HTMLDivElement>(null);
    const [rendered, setRendered] = React.useState(false);

    const {viewBuilder} = React.useContext(LegacyContext);
    React.useEffect(() => {
        if (!viewBuilder) {
            return;
        }
        viewBuilder.build(viewName).then((view: any) => {
            view.setElement(el.current).render();
            setRendered(true);
        });
    }, [viewName]);

    return <StyledPimView className={className} ref={el} rendered={rendered} />;
};
