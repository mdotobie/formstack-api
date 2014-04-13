<?php
require_once dirname(__FILE__) . '/../config.php';
require_once dirname(__FILE__) . '/../FormstackApi.php';

/**
 * This test code runs on PHPUnit 4.0. You will need to install PHPUnit as
 * described at http://phpunit.de/manual/current/en/installation.html before
 * you can successfully run these tests for yourself.
 *
 * NOTE: This Test Code depends on a config file containing constants used throughout
 * this code. You must create and reference your config file with the proper values.
 *
 * NOTE: This Test Code makes numerous API Calls to the Formstack API when run.
 * It's inadvisable to use this test code with Access Token(s) utilized in
 * production, as the Formstack API is rate limited to 14,400 requests a day.
 */

/**
 * @coversDefaultClass \FormstackApi
 */
class WrapperTest extends PHPUnit_Framework_TestCase {

    /**
     * @covers                      ::request
     * @expectedException           Exception
     * @expectedExceptionMessage    You must include an enpoint to request
     */
    public function testRequestEmptyEndpoint() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $wrapper->request('');
    }

    /**
     * @covers                      ::request
     * @expectedException           Exception
     * @expectedExceptionMessage    Your requests must be performed with one of the following
     * verbs: GET, PUT, POST, DELETE.
     */
    public function testRequestBadVerb() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $wrapper->request('/test/endpoint', 'FAIL');
    }

    /**
     * @covers                      ::request
     * @expectedException           Exception
     * @expectedExceptionMessage    Request failed. Exception code contains HTTP Status.
     * @expectedExceptionCode       401
     */
    public function testRequestBadToken() {
        $wrapper = new FormstackApi('fail');
        $wrapper->request('form.json');
    }

    /**
     * @covers  ::getForms
     */
    public function testGetFormsIdealNoFolders() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $forms = $wrapper->getForms();
        $this->assertTrue(is_array($forms));
        $this->assertNotEquals(count($forms), 0);
    }

    /**
     * @covers  ::getForms
     */
    public function testGetFormsIdealFolders() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $folders = $wrapper->getForms(true);
        $this->assertEquals(count($folders), UNEMPTY_FOLDER_COUNT);
    }

    /**
     * @covers  ::getFormDetails
     */
    public function testGetFormDetailsIdeal() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $form = $wrapper->getFormDetails(FORM_DETAILS_ID);
        $this->assertEquals($form->name, FORM_DETAILS_NAME);
    }

    /**
     * @covers  ::getFormDetails
     */
    public function testGetFormDetailsBadFormId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->getFormDetails(1234); // Form that should not exist
        $this->assertEquals($response->status, 'error');
        $this->assertEquals($response->error, 'The form was not found');

        // Form that is more likely to exist but not part of your account
        $response = $wrapper->getFormDetails(FORM_DETAILS_ID + 1);
        $this->assertEquals($response->status, 'error');
        $this->assertEquals($response->error, 'You do not have high enough '
            . 'permissions for this form.'
        );
    }

    /**
     * @covers  ::copyForm
     */
    public function testCopyFormIdeal() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $originalForm = $wrapper->getFormDetails(COPY_FORM_ID);
        $copiedForm = $wrapper->copyForm(COPY_FORM_ID);
        $this->assertStringStartsWith($originalForm->name . ' - COPY', $copiedForm->name);
    }

    /**
     * @covers  ::copyForm
     */
    public function testCopyFormBadFormId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->copyForm(1234); // Form that should not exist
        $this->assertEquals($response->status, 'error');
        $this->assertEquals($response->error, 'A valid form id was not supplied');

        // Form that is more likely to exist but not part of your account
        $response = $wrapper->copyForm(COPY_FORM_ID + 1);
        $this->assertEquals($response->status, 'error');
        $this->assertEquals($response->error, 'A valid form id was not supplied');

    }
}
