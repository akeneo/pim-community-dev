#!groovy

def editions = ["ce", "ee"]
def phpVersions = ["5.4", "5.5", "5.6"]
def storages = ["orm", "odm"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def behatAttempts = 5

stage("Checkout") {
    milestone label: 'input'
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            string(defaultValue: 'odm,orm', description: 'Storage used for the build (comma separated values)', name: 'storages'),
            string(defaultValue: 'ee,ce', description: 'PIM edition the tests should run to (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features')
        ])

        storages = userInput['storages'].tokenize(',')
        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
    }

    milestone label: 'checkout'
    node {
        deleteDir()
        checkout scm
        stash "pim_community_dev"

        if (editions.contains('ee')) {
           checkout([$class: 'GitSCM',
             branches: [[name: '1.4']],
             userRemoteConfigs: [[credentialsId: 'github-credentials', url: 'https://github.com/akeneo/pim-enterprise-dev.git']]
           ])

           stash "pim_enterprise_dev"
        }
    }

    milestone label: 'build'
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
stage("Static analysis") {
    def tasks = [:]
    for (phpVersion in phpVersions) {
        tasks["php-${phpVersion}"] = {
            node('docker') {
                deleteDir()
                docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
                    unstash "pim_community_dev"

                    sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                    sh "touch app/config/parameters_test.yml"
                    sh "./bin/phpunit -c app/phpunit.travis.xml --testsuite PIM_Unit_Test"
                    sh "./bin/phpspec run --no-interaction --format=dot"
                    sh "./bin/php-cs-fixer fix --dry-run -v --diff --config-file=.php_cs.php"
                }
            }
        }
    }

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

    parallel tasks
}

for (storage in storages) {
    for (edition in editions) {
        stage("Acceptance tests for ${edition} ${storage}") {
            node() {
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

                sh "mkdir -p app/build/logs/behat"
                sh "mkdir -p app/build/logs/consumer"
                sh "mkdir -p app/build/screenshots"

                sh "cp behat.ci.yml behat.yml"
                sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE} ${env.BUILD_NUMBER} ${storage} ${features} akeneo/job/pim-community-dev/job/${env.JOB_BASE_NAME} ${behatAttempts} 5.6 5.5 \"${tags}\" \"behat-${edition}-${storage}\""

                archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
                junit 'app/build/logs/behat/*.xml'
            }
        }
    }
}
