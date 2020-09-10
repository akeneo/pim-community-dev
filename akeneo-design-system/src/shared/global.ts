import styled from 'styled-components';

const fontUrl =
  'https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;1,300;1,400;1,700&display=swap';

const StoryStyle = styled.div`
  @import url(${fontUrl});

  font-family: 'Lato', 'Helvetica Neue', Helvetica, Arial, sans-serif;

  color: #67768a;
  font-size: 13px;
  line-height: 20px;

  & > * {
    margin: 10px 0px;
  }
`;

export {StoryStyle};
