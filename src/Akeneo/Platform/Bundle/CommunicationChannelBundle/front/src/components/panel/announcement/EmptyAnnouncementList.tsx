import React from 'react';
import styled from 'styled-components';
import {useIllustration} from 'akeneo-design-system';

type EmptyAnnouncementListProps = {
  text: string;
};

const Container = styled.div`
  margin-top: 50%;
`;

const IllustrationContainer = styled.div`
  margin-left: 83px;
`;

const Text = styled.p`
  margin-top: 20px;
  margin-left: 59px;
  color: #11324d;
  font-size: 17px;
  text-align: center;
  width: 283px;
`;

const EmptyAnnouncementList = ({text}: EmptyAnnouncementListProps): JSX.Element => {
  const NewsIllustration = useIllustration('NewsIllustration');

  return (
    <Container>
      <IllustrationContainer>
        <NewsIllustration width={234} height={196} />
      </IllustrationContainer>
      <Text>{text}</Text>
    </Container>
  );
};

export {EmptyAnnouncementList};
