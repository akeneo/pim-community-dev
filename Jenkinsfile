#!groovy

def editions = ["ce", "ee"]
def storages = ["orm", "odm"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def phpVersion = "5.6"
def mysqlVersion = "5.5"
def esVersion = "none"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            string(defaultValue: 'odm,orm', description: 'Storage used for the build (comma separated values)', name: 'storages'),
            string(defaultValue: 'ee,ce', description: 'PIM edition the tests should run to (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            choice(choices: '5.6\n7.0\n7.1', description: 'PHP version to run behat with', name: 'phpVersion'),
            choice(choices: '5.5\n5.7', description: 'Mysql version to run behat with', name: 'mysqlVersion'),
            choice(choices: 'none\n1.7\n5', description: 'ElasticSearch version to run behat with', name: 'esVersion')
        ])

        storages = userInput['storages'].tokenize(',')
        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
        phpVersion = userInput['phpVersion']
        mysqlVersion = userInput['mysqlVersion']
        esVersion = userInput['esVersion']
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

    if (editions.contains('ee') && storages.contains('odm')) {tasks["behat-ee-odm"] = {runBehatTest("ee", "odm", features, phpVersion, mysqlVersion, esVersion)}}
    if (editions.contains('ee') && storages.contains('orm')) {tasks["behat-ee-orm"] = {runBehatTest("ee", "orm", features, phpVersion, mysqlVersion, esVersion)}}
    if (editions.contains('ce') && storages.contains('odm')) {tasks["behat-ce-odm"] = {runBehatTest("ce", "odm", features, phpVersion, mysqlVersion, esVersion)}}
    if (editions.contains('ce') && storages.contains('orm')) {tasks["behat-ce-orm"] = {runBehatTest("ce", "orm", features, phpVersion, mysqlVersion, esVersion)}}

    parallel tasks
}

def runUnitTest(version) {
    node('docker') {
        deleteDir()
        docker.image("mysql:5.5").withRun("--name phpunit -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim") {
            docker.image("carcel/php:${version}").inside("--link phpunit:phpunit -v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                unstash "pim_community_dev"

                if (version != "5.6") {
                    sh "composer require --no-update alcaeus/mongo-php-adapter"
                }

                sh "composer update --ignore-platform-reqs --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "composer global require friendsofphp/php-cs-fixer ^2.0"
                sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                sh "sed -i 's/database_host:     localhost/database_host:     phpunit/' app/config/parameters_test.yml"
                sh "./app/console --env=test pim:install --force"

                sh "./bin/phpunit -c app/phpunit.travis.xml --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml || true"
                sh "./bin/phpunit -c app/phpunit.travis.xml --testsuite PIM_Integration_Test --log-junit app/build/logs/phpunit_integration.xml || true"
                sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml || true"
                sh "/home/akeneo/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --diff --format=junit --config=.php_cs.dist > app/build/logs/phpcs.xml || true"

                sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${version}] /\" app/build/logs/*.xml"
                junit "app/build/logs/*.xml"
                sh "if test `grep 'status=\"failed\"' app/build/logs/phpunit.xml | wc -l` -ne 0; then exit 1; fi"
                sh "if test `grep 'status=\"failed\"' app/build/logs/phpunit_integration.xml | wc -l` -ne 0; then exit 1; fi"
                sh "if test `grep 'status=\"failed\"' app/build/logs/phpspec.xml | wc -l` -ne 0; then exit 1; fi"
                sh "if test `grep '<failure ' app/build/logs/phpcs.xml | wc -l` -ne 0; then exit 1; fi"
            }
        }
    }
}

def runBehatTest(edition, storage, features, phpVersion, mysqlVersion, esVersion) {
    node() {
        dir("behat-${edition}-${storage}") {
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
            mysqlHostName = "mysql_akeneo_job_pim-community-dev_job_${env.JOB_BASE_NAME}_${env.BUILD_NUMBER}_behat-${edition}-${storage}"

            // Configure the PIM
            sh "cp behat.ci.yml behat.yml"
            sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
            sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
            if ('ce' == edition) {
               sh "printf \"    installer_data: 'PimInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            } else {
               sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            }

            // Activate MongoDB if needed
            if ('odm' == storage) {
               sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
               sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
               sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
               sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
            }

            sh "mkdir -p app/build/logs/behat app/build/logs/consumer app/build/screenshots"
            sh "cp behat.ci.yml behat.yml"
            sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${edition}-${storage} ${env.BUILD_NUMBER} ${storage} ${features} akeneo/job/pim-community-dev/job/${env.JOB_BASE_NAME} 5 ${phpVersion} ${mysqlVersion} \"${tags}\" \"behat-${edition}-${storage}\" -e ${esVersion}"
            archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
            sh "sed -i \"s/ name=\\\"/ name=\\\"[${edition}-${storage}] /\" app/build/logs/behat/*.xml"
            junit 'app/build/logs/behat/*.xml'
            sh "if test `grep 'status=\"failed\"' app/build/logs/behat/*.xml | wc -l` -ne 0; then exit 1; fi"
        }
    }
}
