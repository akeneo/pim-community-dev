#!groovy

stage 'Build'
node {
  step([$class: 'GitHubSetCommitStatusBuilder', statusMessage: [content: 'Building commit...']])

  checkout scm

  step([$class: 'GitHubCommitNotifier', resultOnFailure: 'FAILURE', statusMessage: [content: 'Build finished']])
}

