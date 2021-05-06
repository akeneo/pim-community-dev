import {Project} from '../../../../src/domain';

const buildProjects = (numberOfProjectsToBuild: number): Project[] => {
  const projects = [];
  for (let i = 1; i <= numberOfProjectsToBuild; i++) {
    projects.push(aProject(`project${i}`));
  }

  return projects;
};

const aProject = (code: string): Project => {
  return {
    code: code,
    label: code,
    channel: {
      code: 'ecommerce',
      labels: {
        ecommerce: 'eccommerce',
      },
    },
    locale: {
      code: 'en_US',
      label: 'English (US)',
    },
    completeness: {
      ratio_done: 64,
    },
    due_date: '2035-12-24',
    description: "Project's description",
    owner: {
      username: 'julia',
    },
  };
};

export {aProject, buildProjects};
