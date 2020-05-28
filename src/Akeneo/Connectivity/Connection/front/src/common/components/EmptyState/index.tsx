import styled, {Theme} from '../../styled-with-theme';

const EmptyState = styled.div`
    text-align: center;
`;

type Props = {illustration?: keyof Theme['illustration']};
const Illustration = styled.img.attrs<Props>(({theme, width = 200, illustration = 'api'}) => ({
    src: theme.illustration[illustration],
    width,
}))<Props>``;

const Heading = styled.h1<{fontSize?: keyof Theme['fontSize']}>`
    color: ${({theme}) => theme.color.grey140};
    font-size: ${({theme, fontSize = 'title'}) => theme.fontSize[fontSize]};
    font-weight: normal;
    margin: 0;
    margin-bottom: 10px;
    line-height: 1.2em;
`;

const Caption = styled.p<{fontSize?: keyof Theme['fontSize']}>`
    font-size: ${({theme, fontSize = 'bigger'}) => theme.fontSize[fontSize]};
    line-height: 1.2em;
`;

export {EmptyState, Illustration, Heading, Caption};
