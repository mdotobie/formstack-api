<?php
/**
 * These tests depend on some constants that will need to be set. I'm setting
 * them in a config file that I'm requiring_once.
 */
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../FormstackApi.php';

/**
 * This test code runs on PHPUnit 4.0. You will need to install PHPUnit as
 * described at http://phpunit.de/manual/current/en/installation.html before
 * you can successfully run these tests for yourself.
 */

class WrapperTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException           Exception
     * @expectedExceptionMessage    You must include an enpoint to request
     */
    public function testRequestEmptyEndpoint() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $wrapper->request('');
    }

    /**
     * @expectedException           Exception
     * @expectedExceptionMessage    Your requests must be performed with one of the following
     * verbs: GET, PUT, POST, DELETE.
     */
    public function testRequestBadVerb() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $wrapper->request('/test/endpoint', 'FAIL');
    }

    public function testGetFormsIdealNoFolders() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $forms = $wrapper->getForms();
        $this->assertEquals(count($forms), FORM_COUNT);
    }

    public function testGetFormsIdealFolders() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $folders = $wrapper->getForms(true);
        $this->assertEquals(count($folders), UNEMPTY_FOLDER_COUNT);
    }
}
