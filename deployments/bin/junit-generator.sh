#!/usr/bin/env bash

BASE_CONTENT=<<<EOF
<?xml version="1.0" encoding="UTF-8"?> 
<testsuites tests="1" errors="0" warnings="0" failures="0" skipped="0" time="0">
    <testsuite name="test-1" tests="2" failures="17" time="0.001">
        <testcase id="codereview.cobol.rules.ProgramIdRule" name="Utilisez un nom de programme correspondant au nom du fichier source" time="0.001">
            <failure message="PROGRAM.cbl:2 Utilisez un nom de programme correspondant au nom du fichier source" type="WARNING">
            </failure>
        </testcase>
        <testcase id="codereview.cobol.rules.ProgramIdRule" name="Utilisez un nom de programme correspondant au nom du fichier source" time="0.001">
            <failure message="PROGRAM.cbl:2 Utilisez un nom de programme correspondant au nom du fichier source" type="WARNING">
            </failure>
        </testcase>
    </testsuite>
    <testsuite name="Revue de code COBOL" tests="45" failures="17" time="0.001">
        <testcase id="codereview.cobol.rules.ProgramIdRule" name="Utilisez un nom de programme correspondant au nom du fichier source" time="0.001">
            <failure message="PROGRAM.cbl:2 Utilisez un nom de programme correspondant au nom du fichier source" type="WARNING">
            </failure>
        </testcase>
    </testsuite>
</testsuites>
EOF;

help()
{
   # Display Help
   echo "Build junit xml file"
   echo
   echo "Usage :"
   echo "    $(basename $0) <flags> [output-file]"
   echo
   echo "flags:"
   echo "    -h      Show this help"
   echo "    -t      Add a new testsuite"
   echo "    -c      Add a new test case"
   echo "    -f      Set last testcase as a failure"
   echo "    -v      Verbose mode."
   echo "    -o      (mandatory) Output file"
   echo
}

Name="zae"
# Get the options
while getopts ":hs:t:c:n:" option; do
    case $option in
        h) # display Help
            help
            exit;;
        n) # Enter a name
            Name=$OPTARG;;
        \?) # Invalid option
            echo "Error: Invalid option"
            exit;;
   esac
done

echo "Hello ${Name}!"