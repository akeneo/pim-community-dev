#!groovy

stage 'Build'
node('debian-8') {
  step([$class: 'GitHubSetCommitStatusBuilder'])

  deleteDir()
  checkout scm
  sh "php /usr/local/bin/composer update -o -n --no-progress --prefer-dist --ignore-platform-reqs"
  stash "project_files"
}

for (platform in [ 'php-5.4', 'php-5.5', 'php-5.6', 'php-7.0' ]) {
  stage 'Unit Tests for platform ' + platform
  parallel (
     phpunit: {
        node(platform) {
           unstash "project_files"
           sh "php5 bin/phpunit -c app/phpunit.travis.xml --testsuite PIM_Unit_Test"
        }
     },
     phpspec: {
        node(platform) {
           unstash "project_files"
           sh "bin/phpspec run --format junit > app/build/phpspec.xml"
        }
     },
     phpcsfixer: {
        node(platform) {
           unstash "project_files"
           sh "curl http://get.sensiolabs.org/php-cs-fixer-v1.11.phar -o php-cs-fixer"
           sh "php5 php-cs-fixer fix --dry-run -v --diff --config-file=.php_cs.php"
        }
     }
  )
}

stage 'Frontend tests'
node('grunt') {
  unstash "project_files"
  sh "grunt --gruntfile app/build/Gruntfile.js travis"
}

stage 'Build parameters'
timeout(time:7, unit:'DAYS') {
  def userInput = input(id: 'userInput', message: 'Launch acceptance tests?', parameters: [
    [
      $class: 'TextParameterDefinition',
      name: 'branch',
      defaultValue: 'master',
      description: 'Branch to build'
    ],
    [
      $class: 'TextParameterDefinition',
      name: 'owner',
      defaultValue: 'akeneo',
      description: 'The repo\'s owner on github'
    ],
    [
      $class: 'ChoiceParameterDefinition',
      name: 'pim_version',
      choices: 'pim-community-dev\npim-enterprise-dev'
    ],
    [
      $class: 'ChoiceParameterDefinition',
      name: 'storage',
      choices: 'orm\nodm',
      description: 'Storage used for the build, Mysql Or MongoDb'
    ],
    [
      $class: 'TextParameterDefinition',
      name: 'features',
      defaultValue: 'features,vendor/akeneo/pim-community-dev/features',
      description: 'Features directories to build'
    ],
    [
      $class: 'TextParameterDefinition',
      name: 'ce_branch',
      defaultValue: 'dev-master',
      description: 'Community Edition branch used for the build. If blank, it will use the original composer.json. Leave it empty if you run a community edition build. (Examples : 1.4.x-dev, dev-master, dev-PIM-666)'
    ],
    [
      $class: 'ChoiceParameterDefinition',
      name: 'priority',
      choices: '5\n1\n2\n3\n4\n6\n7\n8\n9',
      description: 'Smaller the better, (And no, that\'s not what she said)'
    ],
    [
      $class: 'ChoiceParameterDefinition',
      name: 'attempts',
      choices: '3\n1\n2\n4\n5'
    ],
    [
      $class: 'TextParameterDefinition',
      name: 'ce_owner',
      defaultValue: 'akeneo',
      description: 'Community Edition owner used for the build'
    ],
    [
      $class: 'ChoiceParameterDefinition',
      name: 'php_version',
      choices: '5.6\n7.0',
      description: 'PHP version to run the tests with'
    ],
    [
      $class: 'ChoiceParameterDefinition',
      name: 'mysql_version',
      choices: '5.5\n5.7',
      description: 'MySQL version to run the tests with'
    ],
    [
      $class: 'TextParameterDefinition',
      name: 'tags',
      defaultValue: '~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app',
      description: 'Tags definition for Behat'
    ]
  ])
}
echo ("Env: "+userInput['php_version'])

stage 'Acceptance Tests'
node() {
  echo ("Env: "+userInput['php_version'])
  unstash "project_files"
  sh "/usr/bin/php7.0 /var/lib/distributed-ci/dci-master/bin/build -t ${userInput.tags} -b ${userInput.ce_branch} -u "+$userInput['ce_owner']+" -p "+$userInput['php_version']+" -m "+$userInput['mysql_version']+" "+env.WORKSPACE+" "+env.BUILD_NUMBER+" "+$userInput['pim_version']+" "+$userInput['storage']+" "+$userInput['features']+" "+env.JOB_NAME+" "+$userInput['attempts']
}

stage 'Results'
node() {
  step([$class: 'ArtifactArchiver', allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png,app/build/logs/consumer/*.log', defaultExcludes: false, excludes: null])
  step([$class: 'JUnitResultArchiver', testResults: 'app/build/phpunit.xml, app/build/phpspec.xml, app/build/logs/behat/*.xml'])
  step([$class: 'GitHubCommitStatusSetter', resultOnFailure: 'FAILURE', statusMessage: [content: 'Build finished']])
}
