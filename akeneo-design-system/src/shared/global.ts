import {createGlobalStyle, css} from 'styled-components';

const fontUrl =
  'https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap';

const bodyStyles = css`
  @import url(${fontUrl});

  font-family: 'Lato', sans-serif;
`;

const GlobalStyle = createGlobalStyle`
 body {
   ${bodyStyles}
 }
`;

export {GlobalStyle};
