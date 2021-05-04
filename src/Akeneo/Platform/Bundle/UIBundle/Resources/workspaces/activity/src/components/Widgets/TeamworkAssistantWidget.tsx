import React, {useEffect, useState} from 'react';
import {getColor, Helper, Link, ProjectIllustration, SectionTitle} from 'akeneo-design-system';
import styled from 'styled-components';
import {NoDataSection, NoDataText, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useGetProject, useGetProjectCompleteness} from '../../hooks';
import {ProjectCompletenessType} from '../../domain';
import {ContributorsDropdown, ProjectDetails, ProjectsDropdown} from './TeamworkAssistant';

const TeamworkAssistantWidget = () => {
  const translate = useTranslate();
  const router = useRouter();
  const [currentProjectCode, setCurrentProjectCode] = useState<string>('');
  const [currentContributorUsername, setCurrentContributorUsername] = useState<string | null>(null);

  const {project, isLoading} = useGetProject(currentProjectCode);
  const projectCompleteness: ProjectCompletenessType | null = useGetProjectCompleteness(
    currentProjectCode,
    currentContributorUsername
  );

  useEffect(() => {
    if (project !== null) {
      setCurrentProjectCode(project.code);
    }
  }, [project]);

  return (
    <Container id="teamwork-assistant-widget">
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_dashboard.widget.teamwork_assistant.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        {project !== null && <ProjectsDropdown project={project} setCurrentProjectCode={setCurrentProjectCode} />}
        {project !== null && (
          <ContributorsDropdown
            setCurrentContributorUsername={setCurrentContributorUsername}
            currentProjectCode={currentProjectCode}
          />
        )}
      </SectionTitle>

      <Helper level="info">
        <HelperContent
          dangerouslySetInnerHTML={{
            __html: translate('pim_dashboard.widget.teamwork_assistant.helper', {
              link: 'https://help.akeneo.com/pim/serenity/articles/what-is-a-project.html',
            }),
          }}
        />
      </Helper>

      {!project && !projectCompleteness && isLoading && (
        <div style={{position: 'relative', minHeight: '80px'}}>
          <div className="AknLoadingMask AknProjectWidget-loadingMask" />
        </div>
      )}

      {!project && !projectCompleteness && !isLoading && (
        <NoDataSection style={{marginTop: 0}}>
          <ProjectIllustration size={128} />
          <NoDataText style={{fontSize: '15px'}}>
            <p>{translate('pim_dashboard.widget.teamwork_assistant.no_project.title')}</p>
            <p>
              <PimHelperLink href={`#${router.generate('pim_enrich_product_index')}`}>
                {translate('pim_dashboard.widget.teamwork_assistant.no_project.subtitle')}
              </PimHelperLink>
            </p>
          </NoDataText>
        </NoDataSection>
      )}

      {project && (
        <ProjectDetails
          projectCompleteness={projectCompleteness}
          project={project}
          contributor={currentContributorUsername}
        />
      )}
    </Container>
  );
};

const Container = styled.div`
  margin-bottom: 20px;
`;

const HelperContent = styled.span`
  a {
    color: ${getColor('brand', 100)};

    &:hover {
      color: ${getColor('brand', 120)};
    }
  }
`;

const PimHelperLink = styled(Link)`
  &:hover {
    text-decoration: underline;
  }
`;

export {TeamworkAssistantWidget};
