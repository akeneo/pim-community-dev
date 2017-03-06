#!groovy

def editions = ["ce"]
def phpVersion = "5.6"
def features = "features"
def launchUnitTests = "yes"
def launchIntegrationTests = "yes"
def launchBehatTests = "yes"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            choice(choices: 'yes\nno', description: 'Run unit tests', name: 'launchUnitTests'),
            choice(choices: 'yes\nno', description: 'Run integration tests', name: 'launchIntegrationTests'),
            choice(choices: 'yes\nno', description: 'Run functional tests', name: 'launchBehatTests'),
            string(defaultValue: 'ee,ce', description: 'PIM edition the tests should run to (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            choice(choices: '5.6\n7.0\n7.1', description: 'PHP version to run behat with', name: 'phpVersion'),
        ])

        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
        phpVersion = userInput['phpVersion']
        launchUnitTests = userInput['launchUnitTests']
        launchIntegrationTests = userInput['launchIntegrationTests']
        launchBehatTests = userInput['launchBehatTests']
    }
    milestone 2

    node {
        deleteDir()
        checkout scm
        stash "pim_community_dev"

        if (editions.contains('ee') && 'yes' == launchBehatTests) {
           checkout([$class: 'GitSCM',
             branches: [[name: 'master']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           stash "pim_enterprise_dev"
        }
    }

    checkouts = [:];
    checkouts['community'] = {
        node('docker') {
            deleteDir()
            docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"
                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "app/console oro:requirejs:generate-config"
                sh "app/console assets:install"
                stash "pim_community_dev_full"
            }
            deleteDir()
        }
    }
    if (editions.contains('ee') && 'yes' == launchBehatTests) {
        checkouts['enterprise'] = {
            node('docker') {
                deleteDir()
                docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                    unstash "pim_enterprise_dev"
                    sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    sh "app/console oro:requirejs:generate-config"
                    sh "app/console assets:install"
                    stash "pim_enterprise_dev_full"
                }
                deleteDir()
            }
        }
    }

    parallel checkouts
}

if (launchUnitTests.equals("yes")) {
    stage("Unit tests and Code style") {
        def tasks = [:]

        tasks["phpunit-5.6"] = {runPhpUnitTest("5.6")}
        tasks["phpunit-7.0"] = {runPhpUnitTest("7.0")}
        tasks["phpunit-7.1"] = {runPhpUnitTest("7.1")}

        tasks["phpspec-5.6"] = {runPhpSpecTest("5.6")}
        tasks["phpspec-7.0"] = {runPhpSpecTest("7.0")}
        tasks["phpspec-7.1"] = {runPhpSpecTest("7.1")}

        tasks["php-cs-fixer-5.6"] = {runPhpCsFixerTest("5.6")}
        tasks["php-cs-fixer-7.0"] = {runPhpCsFixerTest("7.0")}
        tasks["php-cs-fixer-7.1"] = {runPhpCsFixerTest("7.1")}

        tasks["grunt"] = {runGruntTest()}

        parallel tasks
    }
}

if (launchIntegrationTests.equals("yes")) {
    stage("Integration tests") {
        def tasks = [:]

        tasks["phpunit-5.6"] = {runIntegrationTest("5.6")}
        tasks["phpunit-7.0"] = {runIntegrationTest("7.0")}
        tasks["phpunit-7.1"] = {runIntegrationTest("7.1")}

        parallel tasks
    }
}

if (launchBehatTests.equals("yes")) {
    stage("Functional tests") {
        def tasks = [:]

        if (editions.contains('ee')) {tasks["behat-ee"] = {runBehatTest("ee", features, phpVersion)}}
        if (editions.contains('ce')) {tasks["behat-ce"] = {runBehatTest("ce", features, phpVersion)}}

        parallel tasks
    }
}

def runGruntTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image('digitallyseamless/nodejs-bower-grunt').inside("") {
                unstash "pim_community_dev_full"
                sh "npm install"
                sh "grunt --force"
            }
        } finally {
            deleteDir()
        }
    }
}

def runPhpUnitTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"
                sh "./bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runIntegrationTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("elasticsearch:5.2").withRun("--name elasticsearch") {
                docker.image("mysql:5.7").withRun("--name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim", "--sql_mode=ERROR_FOR_DIVISION_BY_ZERO,NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION") {
                    docker.image("carcel/php:${phpVersion}").inside("--link mysql:mysql --link elasticsearch:elasticsearch -v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                        unstash "pim_community_dev"

                        sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                        sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                        sh "sed -i \"s#database_host: .*#database_host: mysql#g\" app/config/parameters_test.yml"
                        sh "sed -i \"s#pim_es_host: .*#pim_es_host: elasticsearch#g\" app/config/parameters_test.yml"

                        sh "./app/console --env=test pim:install --force"

                        sh "mkdir -p app/build/logs/"
                        sh "./bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Integration_Test --log-junit app/build/logs/phpunit_integration.xml"
                    }
                }
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runPhpSpecTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"
                sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runPhpCsFixerTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "mkdir -p app/build/logs/"
                sh "./bin/php-cs-fixer fix --diff --dry-run --format=junit --config=.php_cs.php > app/build/logs/phpcs.xml"
            }
        } finally {
            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            deleteDir()
        }
    }
}

def runBehatTest(edition, features, phpVersion) {
    node() {
        dir("behat-${edition}") {
            deleteDir()
            if ('ce' == edition) {
               unstash "pim_community_dev_full"
               tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
            } else {
                unstash "pim_enterprise_dev_full"
                dir('vendor/akeneo/pim-community-dev') {
                    deleteDir()
                    unstash "pim_community_dev"
                }
                tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"
            }

            // Create mysql hostname (MySQL docker container name)
            mysqlHostName = "mysql_${env.JOB_NAME}_${env.BUILD_NUMBER}_behat-${edition}".replaceAll( '/', '_' )

            // Configure the PIM
            sh "cp behat.ci.yml behat.yml"
            sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
            sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
            sh "sed -i \"s#pim_es_host: .*#pim_es_host: elasticsearch#g\" app/config/parameters_test.yml"
            if ('ce' == edition) {
               sh "printf \"    installer_data: 'PimInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            } else {
               sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            }

            sh "mkdir -p app/build/logs/behat app/build/logs/consumer app/build/screenshots"
            sh "cp behat.ci.yml behat.yml"

            try {
                sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${edition} ${env.BUILD_NUMBER} orm ${features} ${env.JOB_NAME} 5 ${phpVersion} 5.7 \"${tags}\" \"behat-${edition}\" -e 5.2 --exit_on_failure"
            } finally {
                sh "sed -i \"s/ name=\\\"/ name=\\\"[${edition}] /\" app/build/logs/behat/*.xml"
                junit 'app/build/logs/behat/*.xml'
                archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
                deleteDir()
            }
        }
    }
}
