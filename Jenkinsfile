#!groovy

def storages = ["orm", "odm"]
def features = "features,vendor/akeneo/pim-community-dev/features"
def ceBranch = "1.5.x-dev"
def ceOwner = "akeneo"

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            string(defaultValue: 'odm,orm', description: 'Storage used for the build (comma separated values)', name: 'storages'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            string(defaultValue: '1.5.x-dev', description: 'Community Edition branch used for the build', name: 'ce_branch'),
            string(defaultValue: 'akeneo', description: 'Owner of the repository on GitHub', name: 'ce_owner')
        ])

        storages = userInput['storages'].tokenize(',')
        features = userInput['features']
        ceBranch = userInput['ce_branch']
        ceOwner = userInput['ce_owner']
    }
    milestone 2

    node {
        deleteDir()
        checkout scm
        stash "project_files"
    }

    node('docker') {
        deleteDir()
        docker.image("carcel/php:5.6").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
            unstash "project_files"
            sh "composer require --no-update \"${ceOwner}/pim-community-dev\":\"${ceBranch}\""
            sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
            sh "app/console oro:requirejs:generate-config"
            sh "app/console assets:install"
            stash "project_files"
        }
    }
}

// Prepare all tests definition in advance to run them in parallel
stage("Integration tests") {
    def tasks = [:]

    tasks["php-5.4"] = {runUnitTest("5.4")}
    tasks["php-5.5"] = {runUnitTest("5.5")}
    tasks["php-5.6"] = {runUnitTest("5.6")}
    tasks["php-7.0"] = {runUnitTest("7.0")}

    tasks['grunt'] = {
        node('docker') {
            deleteDir()
            docker.image('digitallyseamless/nodejs-bower-grunt').inside("") {
                unstash "project_files"
                sh "npm install"
                sh "grunt travis"
            }
        }
    }

    if (storages.contains('odm')) {tasks["behat-odm"] = {runBehatTest("odm", features)}}
    if (storages.contains('orm')) {tasks["behat-orm"] = {runBehatTest("orm", features)}}

    parallel tasks
}

def runUnitTest(phpVersion) {
    node('docker') {
        deleteDir()
        docker.image("carcel/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/akeneo/.composer -e COMPOSER_HOME=/home/akeneo/.composer") {
            unstash "project_files"

            if (phpVersion == "7.0") {
                sh "composer remove --dev --no-update doctrine/mongodb-odm-bundle;"
            }

            sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
            sh "touch app/config/parameters_test.yml"

            sh "mkdir -p app/build/logs"
            sh "./bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml || true"

            if (phpVersion != "5.4") {
                sh "composer global require friendsofphp/php-cs-fixer ^2.0"
                sh "/home/akeneo/.composer/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix --diff --format=junit --config=.php_cs.dist > app/build/logs/phpcs.xml || true"
            }

            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"
            sh "if test `grep 'status=\"failed\"' app/build/logs/phpspec.xml | wc -l` -ne 0; then exit 1; fi"
            sh "if test `grep '<failure ' app/build/logs/phpcs.xml | wc -l` -ne 0; then exit 1; fi"
        }
    }
}

def runBehatTest(storage, features) {
    node() {
        dir("behat-${storage}") {
            deleteDir()
            unstash "project_files"
            tags = "~skip&&~skip-pef&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"

            // Create mysql hostname (MySQL docker container name)
            mysqlHostName = "mysql_akeneo_job_pim-enterprise-dev_job_${env.JOB_BASE_NAME}_${env.BUILD_NUMBER}_behat-${storage}"

            // Configure the PIM
            sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
            sh "sed -i \"s#database_host: .*#database_host: ${mysqlHostName}#g\" app/config/parameters_test.yml"
            sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"

            // Activate MongoDB if needed
            if ('odm' == storage) {
               sh "sed -i \"s@// new Doctrine@new Doctrine@g\" app/AppKernel.php"
               sh "sed -i \"s@# mongodb_database: .*@mongodb_database: akeneo_pim@g\" app/config/pim_parameters.yml"
               sh "sed -i \"s@# mongodb_server: .*@mongodb_server: 'mongodb://mongodb:27017'@g\" app/config/pim_parameters.yml"
               sh "printf \"    pim_catalog_product_storage_driver: doctrine/mongodb-odm\n\" >> app/config/parameters_test.yml"
            }

            sh "mkdir -p app/build/logs/behat app/build/logs/consumer app/build/screenshots"
            sh "cp behat.ci.yml behat.yml"
            sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${storage} ${env.BUILD_NUMBER} ${storage} ${features} akeneo/job/pim-enterprise-dev/job/${env.JOB_BASE_NAME} 5 5.6 5.5 \"${tags}\" \"behat-${storage}\""
            archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'
            sh "sed -i \"s/ name=\\\"/ name=\\\"[${storage}] /\" app/build/logs/behat/*.xml"
            junit 'app/build/logs/behat/*.xml'
            sh "if test `grep 'status=\"failed\"' app/build/logs/behat/*.xml | wc -l` -ne 0; then exit 1; fi"
        }
    }
}
