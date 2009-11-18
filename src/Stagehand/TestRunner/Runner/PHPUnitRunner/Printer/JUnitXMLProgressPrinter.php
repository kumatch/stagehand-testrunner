<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.10.0
 */

require_once 'PHPUnit/Framework/SelfDescribing.php';
require_once 'PHPUnit/Framework/Test.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestFailure.php';
require_once 'PHPUnit/Framework/TestListener.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';
require_once 'PHPUnit/Util/XML.php';

// {{{ Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_JUnitXMLProgressPrinter

/**
 * A result printer for PHPUnit.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_JUnitXMLProgressPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $xmlWriter;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     */
    public function __construct()
    {
        $this->xmlWriter = new XMLWriter();
        $this->xmlWriter->openMemory();
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('testsuites');

        parent::__construct(null);
    }

    // }}}
    // {{{ flush()

    /**
     */
    public function flush()
    {
        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
        echo $this->xmlWriter->outputMemory();

        parent::flush();
    }

    // }}}
    // {{{ addError()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        if ($test instanceof PHPUnit_Framework_SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }

        $buffer .= PHPUnit_Framework_TestFailure::exceptionToString($e) .
            "\n" .
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false);

        $this->xmlWriter->startElement('error');
        $this->xmlWriter->writeAttribute('type', get_class($e));
        $this->xmlWriter->text(PHPUnit_Util_XML::convertToUtf8($buffer));
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ addFailure()

    /**
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        if ($test instanceof PHPUnit_Framework_SelfDescribing) {
            $buffer = $test->toString() . "\n";
        } else {
            $buffer = '';
        }

        $buffer .= PHPUnit_Framework_TestFailure::exceptionToString($e).
            "\n" .
            PHPUnit_Util_Filter::getFilteredStacktrace($e, false);

        $this->xmlWriter->startElement('failure');
        $this->xmlWriter->writeAttribute('type', get_class($e));
        $this->xmlWriter->text(PHPUnit_Util_XML::convertToUtf8($buffer));
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ addIncompleteTest()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->xmlWriter->startElement('error');
        $this->xmlWriter->writeAttribute('type', get_class($e));
        $this->xmlWriter->text(
            PHPUnit_Util_XML::convertToUtf8(
                "Incomplete Test\n" .
                PHPUnit_Util_Filter::getFilteredStacktrace($e, false)
            )
        );
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ addSkippedTest()

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->xmlWriter->startElement('error');
        $this->xmlWriter->writeAttribute('type', get_class($e));
        $this->xmlWriter->text(
            PHPUnit_Util_XML::convertToUtf8(
                "Skipped Test\n" .
                PHPUnit_Util_Filter::getFilteredStacktrace($e, FALSE)
            )
        );
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ startTestSuite()

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->xmlWriter->startElement('testsuite');
        $this->xmlWriter->writeAttribute('name', $suite->getName());
        $this->xmlWriter->writeAttribute('tests', count($suite));

        if (class_exists($suite->getName(), false)) {
            try {
                $class = new ReflectionClass($suite->getName());

                $this->xmlWriter->writeAttribute('file', $class->getFileName());

                $packageInformation = PHPUnit_Util_Class::getPackageInformation(
                    $suite->getName(), $class->getDocComment()
                );

                if (strlen($packageInformation['namespace'])) {
                    $this->xmlWriter->writeAttribute(
                        'namespace', $packageInformation['namespace']
                    );
                }

                if (strlen($packageInformation['fullPackage'])) {
                    $this->xmlWriter->writeAttribute(
                        'fullPackage', $packageInformation['fullPackage']
                    );
                }

                if (strlen($packageInformation['category'])) {
                    $this->xmlWriter->writeAttribute(
                        'category', $packageInformation['category']
                    );
                }

                if (strlen($packageInformation['package'])) {
                    $this->xmlWriter->writeAttribute(
                        'package', $packageInformation['package']
                    );
                }

                if (strlen($packageInformation['subpackage'])) {
                    $this->xmlWriter->writeAttribute(
                        'subpackage', $packageInformation['subpackage']
                    );
                }
            } catch (ReflectionException $e) {
            }
        }

        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ endTestSuite()

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ startTest()

    /**
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->xmlWriter->startElement('testcase');
        $this->xmlWriter->writeAttribute('name', $test->getName());

        if ($test instanceof PHPUnit_Framework_TestCase) {
            $class      = new ReflectionClass($test);
            $methodName = $test->getName();

            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($test->getName());

                $this->xmlWriter->writeAttribute('class', $class->getName());
                $this->xmlWriter->writeAttribute('file', $class->getFileName());
                $this->xmlWriter->writeAttribute('line', $method->getStartLine());
            }
        }

        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ endTest()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */