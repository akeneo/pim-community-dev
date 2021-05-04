import React, {FC, useLayoutEffect, useRef, useState} from 'react';
import {Project, ProjectCompletenessType} from '../../../domain';
import styled from 'styled-components';
import {useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Link, Locale, getColor, getFontSize} from 'akeneo-design-system';

const DateFormatter = require('pim/formatter/date');
const DateContext = require('pim/date-context');

type ProjectCompletenessProps = {
  projectCompleteness: ProjectCompletenessType | null;
  project: Project;
  contributor: string | null;
};

const ProjectDetails: FC<ProjectCompletenessProps> = ({projectCompleteness, project, contributor}) => {
  const translate = useTranslate();
  const router = useRouter();
  const userContext = useUserContext();
  const uiLocale = userContext.get('uiLocale');
  const channelLabelRef = useRef<HTMLDivElement>(null);
  const localeLabelRef = useRef<HTMLDivElement>(null);
  const [displayChannelLocaleSeparator, setDisplayChannelLocaleSeparator] = useState<boolean>(true);

  useLayoutEffect(() => {
    if (!channelLabelRef.current || !localeLabelRef.current) {
      return;
    }
    setDisplayChannelLocaleSeparator(
      channelLabelRef.current.getBoundingClientRect().top === localeLabelRef.current.getBoundingClientRect().top
    );
  }, [channelLabelRef, localeLabelRef, project]);

  const generateGridLink = (status: string) => {
    const isOwner = project.owner.username === userContext.get('username');
    const noContributor = null === contributor;

    return (
      '#' +
      router.generate('teamwork_assistant_project_show', {
        identifier: project.code,
        status: noContributor && isOwner ? 'owner-' + status : 'contributor-' + status,
      })
    );
  };

  const displayLink = contributor === userContext.get('username') || contributor === null;

  const todoProjectScope =
    projectCompleteness &&
    `${Math.round(projectCompleteness.ratio_todo)}% ${translate('teamwork_assistant.widget.completeness.to_start')}`;
  const inProgressProjectScope =
    projectCompleteness &&
    `${Math.round(projectCompleteness.ratio_in_progress)}% ${translate(
      'teamwork_assistant.widget.completeness.in_progress'
    )}`;
  const doneProjectScope =
    projectCompleteness &&
    `${Math.round(projectCompleteness.ratio_done)}% ${translate('teamwork_assistant.widget.completeness.done')}`;

  const TodoCompletenessComponent: any = displayLink ? TodoCompletenessWithLink : TodoCompletenessReadonly;
  const InProgressCompletenessComponent: any = displayLink
    ? InProgressCompletenessWithLink
    : InProgressCompletenessReadonly;
  const DoneCompletenessComponent: any = displayLink ? DoneCompletenessWithLink : DoneCompletenessReadonly;

  return (
    <Container>
      <ProjectCompleteness>
        {((projectCompleteness && !projectCompleteness.is_completeness_computed) || !projectCompleteness) && (
          <div className="AknLoadingMask AknProjectWidget-loadingMask" />
        )}
        {projectCompleteness && projectCompleteness.is_completeness_computed && (
          <>
            <TodoCompletenessComponent href={generateGridLink('todo')} decorated className="project-datagrid-link">
              <Label>{translate('teamwork_assistant.widget.todo')}</Label>
              <ProductsNumber className="teamwork-assistant-completeness-todo">
                {projectCompleteness.products_count_todo}
              </ProductsNumber>
              <CompletenessProgress>{todoProjectScope}</CompletenessProgress>
            </TodoCompletenessComponent>
            <InProgressCompletenessComponent
              href={generateGridLink('inprogress')}
              decorated
              className="project-datagrid-link"
            >
              <Label>{translate('teamwork_assistant.widget.in_progress')}</Label>
              <ProductsNumber className="teamwork-assistant-completeness-in-progress">
                {projectCompleteness.products_count_in_progress}
              </ProductsNumber>
              <CompletenessProgress>{inProgressProjectScope}</CompletenessProgress>
            </InProgressCompletenessComponent>
            <DoneCompletenessComponent href={generateGridLink('done')} decorated className="project-datagrid-link">
              <Label>{translate('teamwork_assistant.widget.done')}</Label>
              <ProductsNumber className="teamwork-assistant-completeness-done">
                {projectCompleteness.products_count_done}
              </ProductsNumber>
              <CompletenessProgress>{doneProjectScope}</CompletenessProgress>
            </DoneCompletenessComponent>
          </>
        )}
      </ProjectCompleteness>

      <ProjectMetadata>
        <ChannelLocaleContainer>
          <ChannelName ref={channelLabelRef}>
            {project.channel.labels[uiLocale] || `[${project.channel.code}]`}
          </ChannelName>
          {displayChannelLocaleSeparator && <div>&nbsp;|&nbsp;</div>}
          <Locale code={project.locale.code} languageLabel={project.locale.label} ref={localeLabelRef} />
        </ChannelLocaleContainer>
        <div>
          {translate('teamwork_assistant.widget.due_date')} -&nbsp;
          {DateFormatter.format(project.due_date, 'yyyy-MM-dd', DateContext.get('date').format)}
        </div>
        <Description>{project.description}</Description>
      </ProjectMetadata>
    </Container>
  );
};

const Container = styled.div`
  display: flex;
  align-items: center;
  margin: 20px 0 0 0;
`;

const ProjectCompleteness = styled.div`
  display: flex;
  width: 72%;
  position: relative;
  min-height: 80px;
`;

const StatusCompletenessWithLink = styled(Link)`
  flex-basis: 33%;
  padding-left: 18px;
  text-decoration: none;

  :hover {
    border-left-width: 6px;
    transition: border-left-width 0.2s linear, padding-left 0.2s linear;
    padding-left: 14px;
  }
`;

const StatusCompleteness = styled.div`
  flex-basis: 33%;
  padding-left: 18px;
`;

const TodoCompletenessWithLink = styled(StatusCompletenessWithLink)`
  border-left: 2px ${getColor('red', 100)} solid;
`;

const InProgressCompletenessWithLink = styled(StatusCompletenessWithLink)`
  border-left: 2px ${getColor('yellow', 100)} solid;
`;

const DoneCompletenessWithLink = styled(StatusCompletenessWithLink)`
  border-left: 2px ${getColor('green', 100)} solid;
`;

const TodoCompletenessReadonly = styled(StatusCompleteness)`
  border-left: 2px ${getColor('red', 100)} solid;
`;

const InProgressCompletenessReadonly = styled(StatusCompleteness)`
  border-left: 2px ${getColor('yellow', 100)} solid;
`;

const DoneCompletenessReadonly = styled(StatusCompleteness)`
  border-left: 2px ${getColor('green', 100)} solid;
`;

const ProjectMetadata = styled.div`
  width: 28%;
  align-self: stretch;
  border-left: 1px ${getColor('grey', 80)} solid;
  padding-left: 20px;
`;

const ChannelLocaleContainer = styled.div`
  display: flex;
  flex-wrap: wrap;
`;

const ChannelName = styled.span`
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
`;

const Label = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

const ProductsNumber = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  margin: 10px 0;
`;

const Description = styled.div`
  :not(:empty) {
    margin-top: 15px;
    display: -webkit-box;
    -webkit-line-clamp: 10; /*Supported by firefox*/
    line-clamp: 10;
    -webkit-box-orient: vertical; /*Supported by firefox*/
    box-orient: vertical;
    overflow: hidden;
  }
`;

const CompletenessProgress = styled.div`
  font-size: ${getFontSize('small')};
  text-transform: lowercase;

  ${StatusCompletenessWithLink} & {
    color: ${getColor('blue', 100)};
    text-decoration: underline;
  }

  ${StatusCompletenessWithLink}:hover & {
    color: ${getColor('brand', 120)};
    text-decoration: underline;
  }

  ${StatusCompletenessWithLink}:active & {
    color: ${getColor('brand', 140)};
    text-decoration: underline;
  }
`;

export {ProjectDetails};
