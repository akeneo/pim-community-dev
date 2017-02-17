#!groovy

def editions = ["ce", "ee"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def phpVersion = "5.6"
def mysqlVersion = "5.5"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            string(defaultValue: 'ee,ce', description: 'PIM edition the tests should run to (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            choice(choices: '5.6\n7.0\n7.1', description: 'PHP version to run behat with', name: 'phpVersion'),
            choice(choices: '5.5\n5.7', description: 'Mysql version to run behat with', name: 'mysqlVersion'),
        ])

        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
        phpVersion = userInput['phpVersion']
        mysqlVersion = userInput['mysqlVersion']
    }
    milestone 2

    node {
        deleteDir()
        checkout scm
        stash "pim_community_dev"

        if (editions.contains('ee')) {
           checkout([$class: 'GitSCM',
             branches: [[name: '1.6']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           stash "pim_enterprise_dev"
        }
    }

    parallel community: {
        node('docker') {
            deleteDir()
            docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"
                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "app/console oro:requirejs:generate-config"
                sh "app/console assets:install"
                stash "pim_community_dev_full"
            }
        }
    }, enterprise: {
        if (editions.contains('ee')) {
            node('docker') {
                deleteDir()
                docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                    unstash "pim_enterprise_dev"
                    sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    sh "app/console oro:requirejs:generate-config"
                    sh "app/console assets:install"
                    stash "pim_enterprise_dev_full"
                }
            }
        }
    }
}

// Prepare all tests definition in advance to run them in parallel
stage("Integration tests") {
    def tasks = [:]

    tasks["php-5.6"] = {runUnitTest("5.6")}
    tasks["php-7.0"] = {runUnitTest("7.0")}
    tasks["php-7.1"] = {runUnitTest("7.1")}

    tasks['grunt'] = {
        node('docker') {
            deleteDir()
            docker.image('digitallyseamless/nodejs-bower-grunt').inside("") {
                unstash "pim_community_dev_full"

                sh "npm install"
                sh "grunt travis"
            }
        }
    }

    if (editions.contains('ee')) {tasks["behat-ee-orm"] = {runBehatTest("ee", features, phpVersion, mysqlVersion)}}
    if (editions.contains('ce')) {tasks["behat-ce-orm"] = {runBehatTest("ce", features, phpVersion, mysqlVersion)}}

    parallel tasks
}

def runUnitTest(phpVersion) {
    node('docker') {
        deleteDir()
        docker.image("mysql:5.5").withRun("--name phpunit_mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim") {
            docker.image("elasticsearch:1.7").withRun("--name phpunit_elasticsearch") {
                docker.image("carcel/php:${phpVersion}").inside("--link phpunit_mysql:phpunit_mysql --link phpunit_elasticsearch:phpunit_elasticsearch -v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                    unstash "pim_community_dev"

                    sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                    sh "sed -i \"s#database_host: .*#database_host: phpunit_mysql#g\" app/config/parameters_test.yml"
                    sh "sed -i \"s#pim_es_host: .*#pim_es_host: phpunit_elasticsearch#g\" app/config/parameters_test.yml"
                    sh "./app/console --env=test pim:install --force"

                    sh "./bin/phpunit -c app/phpunit.travis.xml --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml || true"
                    sh "./bin/phpunit -c app/phpunit.travis.xml --testsuite PIM_Integration_Test --log-junit app/build/logs/phpunit_integration.xml || true"
                    sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml || true"
                    sh "./bin/php-cs-fixer fix --diff --format=junit --config=.php_cs.dist > app/build/logs/phpcs.xml || true"

                    sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
                    junit "app/build/logs/*.xml"
                    sh "if test `grep 'status=\"failed\"' app/build/logs/phpunit.xml | wc -l` -ne 0; then exit 1; fi"
                    sh "if test `grep 'status=\"failed\"' app/build/logs/phpunit_integration.xml | wc -l` -ne 0; then exit 1; fi"
                    sh "if test `grep 'status=\"failed\"' app/build/logs/phpspec.xml | wc -l` -ne 0; then exit 1; fi"
                    sh "if test `grep '<failure ' app/build/logs/phpcs.xml | wc -l` -ne 0; then exit 1; fi"
                }
            }
        }
    }
}

def runBehatTest(edition, features, phpVersion, mysqlVersion) {
    node() {
        dir("behat-${edition}-orm") {
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
            mysqlHostName = "mysql_akeneo_job_pim-community-dev_job_${env.JOB_BASE_NAME}_${env.BUILD_NUMBER}_behat-${edition}-orm"

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
            sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${edition}-orm ${env.BUILD_NUMBER} orm ${features} akeneo/job/pim-community-dev/job/${env.JOB_BASE_NAME} 5 ${phpVersion} ${mysqlVersion} \"${tags}\" \"behat-${edition}-orm\" -e 1.7"
            archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
            sh "sed -i \"s/ name=\\\"/ name=\\\"[${edition}-orm] /\" app/build/logs/behat/*.xml"
            junit 'app/build/logs/behat/*.xml'
            sh "if test `grep 'status=\"failed\"' app/build/logs/behat/*.xml | wc -l` -ne 0; then exit 1; fi"
        }
    }
}
