import React from 'react';
import {JsonEditor} from 'jsoneditor-react';
import 'jsoneditor-react/es/editor.min.css';
import styled from 'styled-components';
import {PlatformConfiguration} from '../models/PlatformConfiguration';

const Container = styled.div`
  height: 100%;
  & > div {
    height: 100%;
  }
`;

type ManualEditorProps = {
  configuration: PlatformConfiguration;
  onConfigurationChange: (configuration: PlatformConfiguration) => void;
};
const ManualEditor = ({configuration, onConfigurationChange}: ManualEditorProps) => {
  //https://github.com/vankop/jsoneditor-react/blob/HEAD/src/Editor.jsx
  return (
    <Container>
      <JsonEditor
        value={configuration}
        onChange={(event: object) => {
          onConfigurationChange(event as PlatformConfiguration);
        }}
        mode="code"
      />
    </Container>
  );
};

export {ManualEditor};
