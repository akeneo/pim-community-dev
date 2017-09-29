#!groovy

def editions = ["ce"]
String phpVersion = "7.1"
String features = "features"
String launchUnitTests = "yes"
String launchIntegrationTests = "yes"
String launchBehatTests = "yes"

Integer nbAvailableNode = 8
def testFilesCE = []
def testFilesEE = []

stage("Checkout") {
    milestone 1
    if (env.BRANCH_NAME =~ /^PR-/) {
        userInput = input(message: 'Launch tests?', parameters: [
            choice(choices: 'yes\nno', description: 'Run unit tests and code style checks', name: 'launchUnitTests'),
            choice(choices: 'yes\nno', description: 'Run integration tests', name: 'launchIntegrationTests'),
            choice(choices: 'yes\nno', description: 'Run behat tests', name: 'launchBehatTests'),
            string(defaultValue: 'ee,ce', description: 'PIM edition the behat tests should run on (comma separated values)', name: 'editions'),
            string(defaultValue: 'features,vendor/akeneo/pim-community-dev/features', description: 'Behat scenarios to build', name: 'features'),
            choice(choices: '7.1', description: 'PHP version to run behat with', name: 'phpVersion'),
        ])

        editions = userInput['editions'].tokenize(',')
        features = userInput['features']
        phpVersion = userInput['phpVersion']
        launchUnitTests = userInput['launchUnitTests']
        launchIntegrationTests = userInput['launchIntegrationTests']
        launchBehatTests = userInput['launchBehatTests']
    }
    milestone 2

    node('docker') {
        deleteDir()
        checkout scm
        stash "pim_community_dev"
        deleteDir()

        if (editions.contains('ee') && ('yes' == launchBehatTests || 'yes' == launchIntegrationTests)) {
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
            docker.image("akeneo/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/docker/.composer -e COMPOSER_HOME=/home/docker/.composer") {
                unstash "pim_community_dev"

                sh "composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist"
                sh "bin/console assets:install"
                sh "bin/console pim:installer:dump-require-paths"

                stash "pim_community_dev_full"
            }

            sh "mkdir -p /home/akeneo/.yarn-cache"

            docker.image('node:8').inside("-v /home/akeneo/.yarn-cache:/home/node/.yarn-cache -e YARN_CACHE_FOLDER=/home/node/.yarn-cache") {
                unstash "pim_community_dev_full"

                sh "yarn install"
                sh "yarn run webpack"

                stash "pim_community_dev_full"
            }

            unstash "pim_community_dev_full"

            def output = sh (
                returnStdout: true,
                script: 'find src tests -name "*Integration.php" -exec sh -c "grep -Ho \'function test\' {} | uniq -c"  \\; | sed "s/:function test//"'
            )
            def files = output.tokenize('\n')
            for (file in files) {
                def fileInfo = file.tokenize(' ')
                testFilesCE += ["nbTests": fileInfo[0] as Integer , "path": fileInfo[1]]
            }

            deleteDir()
        }
    }
    if (editions.contains('ee') && ('yes' == launchBehatTests || 'yes' == launchIntegrationTests)) {
        checkouts['enterprise'] = {
            node('docker') {
                deleteDir()
                docker.image("akeneo/php:${phpVersion}").inside("-v /home/akeneo/.composer:/home/docker/.composer -e COMPOSER_HOME=/home/docker/.composer") {
                    unstash "pim_enterprise_dev"

                    sh "php -d memory_limit=-1 /usr/local/bin/composer update --optimize-autoloader --no-interaction --no-progress --prefer-dist --no-scripts"

                    dir('vendor/akeneo/pim-community-dev') {
                        deleteDir()
                        unstash "pim_community_dev"
                        sh "php -d memory_limit=-1 /usr/local/bin/composer run-script post-update-cmd"
                    }

                    sh "bin/console assets:install"
                    sh "bin/console pim:installer:dump-require-paths"

                    stash "pim_enterprise_dev_full"
                }

                sh "mkdir -p /home/akeneo/.yarn-cache"

                docker.image('node:8').inside("-v /home/akeneo/.yarn-cache:/home/node/.yarn-cache -e YARN_CACHE_FOLDER=/home/node/.yarn-cache") {
                    unstash "pim_enterprise_dev_full"

                    sh "yarn install"
                    sh "yarn run webpack"

                    stash "pim_enterprise_dev_full"
                }

                unstash "pim_enterprise_dev_full"

                def output = sh (
                    returnStdout: true,
                    script: 'find src -name "*Integration.php" -exec sh -c "grep -Ho \'function test\' {} | uniq -c"  \\; | sed "s/:function test//"'
                )
                def files = output.tokenize('\n')
                for (file in files) {
                    def fileInfo = file.tokenize(' ')
                    testFilesEE += ["nbTests": fileInfo[0] as Integer , "path": fileInfo[1]]
                }

                deleteDir()
            }
        }
    }

    parallel checkouts
}

if ('yes' == launchUnitTests) {
    stage("Unit tests and Code style") {
        def tasks = [:]

        tasks["phpunit-7.1"] = {runPhpUnitTest("7.1")}

        tasks["phpspec-7.1"] = {runPhpSpecTest("7.1")}

        tasks["php-cs-fixer"] = {runPhpCsFixerTest()}

        tasks["php-coupling-detector"] = {runPhpCouplingDetectorTest()}

        tasks["grunt"] = {runGruntTest()}

        parallel tasks
    }
}

if (launchIntegrationTests.equals("yes")) {
    if (editions.contains('ce')) {
        stage("Integration tests CE") {
            def tasks = buildIntegrationTestTasks('7.1', 'ce', nbAvailableNode, testFilesCE)
            parallel tasks
        }
    }

    if (editions.contains('ee')) {
        stage("Integration tests EE") {
            def tasks = buildIntegrationTestTasks('7.1', 'ee', nbAvailableNode, testFilesEE)
            parallel tasks
        }
    }
}

if ('yes' == launchBehatTests) {
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
            docker.image('node:8').inside("") {
                unstash "pim_community_dev_full"
                sh "yarn run lint"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            deleteDir()
        }
    }
}

def runPhpUnitTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:${phpVersion}").inside("") {
                unstash "pim_community_dev_full"

                sh "mkdir -p app/build/logs/"

                sh "./vendor/bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Unit_Test --log-junit app/build/logs/phpunit.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "find app/build/logs -name \"*.xml\" | xargs sed -i \"s/testcase name=\\\"/testcase name=\\\"[phpunit-${phpVersion}] /\""
            junit "app/build/logs/*.xml"

            deleteDir()
        }
    }
}

/**
 * Build a list of tasks to run integration tests on multiple nodes in parallel.
 * Each nodes should run approximately the same number of tests.
 *
 * @param phpVersion      version of php to run the tests with
 * @param edition         edition of the PIM ('ce' or 'ee')
 * @param nbAvailableNode number of available nodes to execute the integration tests
 * @param testFiles       list of file containing the filepath of the file and the number of integration tests in the file
 *                        [
 *                            ["path" : "filePath1", "nbTests" : 10],
 *                            ["path" : "filePath2", "nbTests" : 25]
 *                        ]
 *
 * @return list of tasks to execute in parallel
 */
def buildIntegrationTestTasks(String phpVersion, String edition, Integer nbAvailableNode, def testFiles) {
    def tasks = [:]

    def filesPerNode = getTestFilesPerNode(nbAvailableNode, testFiles)

    Integer nodeId = 1
    for (files in filesPerNode) {
        def listFiles = files

        tasks["integration-${phpVersion}-${nodeId}"] = {runIntegrationTest(phpVersion, edition, listFiles)}
        nodeId++;
    }

    return tasks
}

void runIntegrationTest(String phpVersion, String edition, def testFiles) {
    node('docker') {
        deleteDir()
        sh "docker stop \$(docker ps -a -q) || true"
        sh "docker rm \$(docker ps -a -q) || true"

        try {
            docker.image("elasticsearch:5.5").withRun("--name elasticsearch -e ES_JAVA_OPTS=\"-Xms256m -Xmx256m\"") {
                docker.image("mysql:5.7").withRun("--name mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_USER=akeneo_pim -e MYSQL_PASSWORD=akeneo_pim -e MYSQL_DATABASE=akeneo_pim --tmpfs=/var/lib/mysql/:rw,noexec,nosuid,size=1000m --tmpfs=/tmp/:rw,noexec,nosuid,size=300m") {
                    docker.image("akeneo/php:${phpVersion}").inside("--link mysql:mysql --link elasticsearch:elasticsearch") {
                        if ('ce' == edition) {
                            unstash "pim_community_dev_full"
                        } else {
                            unstash "pim_enterprise_dev_full"
                        }

                        sh "cp app/config/parameters_test.yml.dist app/config/parameters_test.yml"
                        sh "sed -i \"s#database_host: .*#database_host: mysql#g\" app/config/parameters_test.yml"
                        sh "sed -i \"s#index_hosts: .*#index_hosts: 'elasticsearch:9200'#g\" app/config/parameters_test.yml"

                        sh "sleep 20"

                        sh "./bin/console --env=test pim:install --force"

                        sh "mkdir -p app/build/logs/"

                        String testSuiteFiles = ""
                        for (testFile in testFiles) {
                            testSuiteFiles += "<file>../${testFile}</file>"
                        }

                        sh "sed -i \"s#<file></file>#${testSuiteFiles}#\" app/phpunit.xml.dist"

                        sh "php -d error_reporting='E_ALL' ./vendor/bin/phpunit -c app/phpunit.xml.dist --testsuite PIM_Integration_Test --log-junit app/build/logs/phpunit_integration.xml"
                    }
                }
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "sed -i \"s/testcase name=\\\"/testcase name=\\\"[integration-${phpVersion}] /\" app/build/logs/*.xml"
            junit "app/build/logs/*.xml"

            deleteDir()
        }
    }
}

def runPhpSpecTest(phpVersion) {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:${phpVersion}").inside("") {
                unstash "pim_community_dev_full"

                sh "mkdir -p app/build/logs/"

                sh "./vendor/bin/phpspec run --no-interaction --format=junit > app/build/logs/phpspec.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "find app/build/logs/ -name \"*.xml\" | xargs sed -i \"s/testcase name=\\\"/testcase name=\\\"[phpspec-${phpVersion}] /\""
            junit "app/build/logs/*.xml"

            deleteDir()
        }
    }
}

def runPhpCsFixerTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:7.1").inside("") {
                unstash "pim_community_dev_full"

                sh "mkdir -p app/build/logs/"

                sh "./vendor/bin/php-cs-fixer fix --diff --dry-run --format=junit --config=.php_cs.php > app/build/logs/phpcs.xml"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            sh "find app/build/logs/ -name \"*.xml\" | xargs sed -i \"s/testcase name=\\\"/testcase name=\\\"[php-cs-fixer] /\""
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
               tags = "~skip&&~skip-pef&&~skip-nav&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app"
            } else {
                unstash "pim_enterprise_dev_full"
                tags = "~skip&&~skip-pef&&~skip-nav&&~doc&&~unstable&&~unstable-app&&~deprecated&&~@unstable-app&&~ce"
            }

            // Configure the PIM
            dir("app") {
                sh "ln -s ./../bin/console"
            }

            dir("bin") {
                sh "ln -s ./../vendor/bin/behat"
            }

            sh "cp app/config/parameters.yml.dist app/config/parameters_test.yml"
            sh "sed -i \"s#database_host: .*#database_host: mysql#g\" app/config/parameters_test.yml"
            sh "sed -i \"s#index_hosts: .*#index_hosts: 'elasticsearch: 9200'#g\" app/config/parameters_test.yml"
            if ('ce' == edition) {
               sh "printf \"    installer_data: 'PimInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            } else {
               sh "printf \"    installer_data: 'PimEnterpriseInstallerBundle:minimal'\n\" >> app/config/parameters_test.yml"
            }

            sh "mkdir -p app/build/logs/behat app/build/logs/consumer app/build/screenshots"
            sh "cp behat.ci.yml behat.yml"

            try {
                sh "php /var/lib/distributed-ci/dci-master/bin/build ${env.WORKSPACE}/behat-${edition} ${env.BUILD_NUMBER} orm ${features} ${env.JOB_NAME} 5 ${phpVersion} 5.7 \"${tags}\" \"behat-${edition}\" -e 5.5 --exit_on_failure"
            } finally {
                sh "find app/build/logs/behat/ -name \"*.xml\" | xargs sed -i \"s/ name=\\\"/ name=\\\"[${edition}] /\""
                junit 'app/build/logs/behat/*.xml'
                archiveArtifacts allowEmptyArchive: true, artifacts: 'app/build/screenshots/*.png'

                deleteDir()
            }
        }
    }
}

def runPhpCouplingDetectorTest() {
    node('docker') {
        deleteDir()
        try {
            docker.image("akeneo/php:7.1").inside("-v /home/akeneo/.composer:/home/docker/.composer -e COMPOSER_HOME=/home/docker/.composer") {
                unstash "pim_community_dev_full"

                sh "./vendor/bin/php-coupling-detector detect --config-file=.php_cd.php src"
            }
        } finally {
            sh "docker stop \$(docker ps -a -q) || true"
            sh "docker rm \$(docker ps -a -q) || true"
            sh "docker volume rm \$(docker volume ls -q) || true"

            deleteDir()
        }
    }
}

/**
 * Calculate and return a list of files to execute in each node, in order to execute approximately the same
 * number of integration tests per node.
 *
 * @param nbNode number of available nodes to execute the integration tests
 * @param files   list of file containing the filepath of the file and the number of integration tests in the file
 *                [
 *                    ["path" : "filePath1", "nbTests" : 10],
 *                    ["path" : "filePath2", "nbTests" : 25]
 *                ]
 *
 * @return an array, each entry being a list of filepath to execute in a node.
 *         [
 *             ["filePath1", "filePath2"]
 *             ["filePath3", "filePath4"]
 *         ]
 */
def getTestFilesPerNode(Integer nbNode, def files) {
    def filesPerNode = []

    Integer nbIntegrationTests = 0;
    for (file in files) {
        nbIntegrationTests += file["nbTests"]
    }
    Integer nbTestsPerNode = nbIntegrationTests.intdiv(nbNode) + 1

    Integer nodeTestCounter = 0
    def nodeFiles = []

    for (file in files) {
        nodeTestCounter += file["nbTests"]
        nodeFiles += file["path"]

        if (nodeTestCounter > nbTestsPerNode) {
            filesPerNode << nodeFiles

            nodeFiles = []
            nodeTestCounter = 0
        }
    }

    if (nodeTestCounter > 0) {
        filesPerNode << nodeFiles
    }

    return filesPerNode
}
