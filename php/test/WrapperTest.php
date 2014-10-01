<?php
require_once dirname(__FILE__) . '/config.php';
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
 *
 * NOTE: Some of these API requests are destructive. Ensure that the constants
 * defined in your own config.php file are for Submissions/Forms/etc. that you
 * are OK with losing.
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
     * @covers                      ::getFormDetails
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Form ID must be numeric
     */
    public function testGetFormDetailsNonNumericFormId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->getFormDetails(FORM_DETAILS_ID . 'FAIL');
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
     * @covers                      ::getFormDetails
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Form ID must be numeric
     */
    public function testCopyFormNonNumericFormId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->copyForm(COPY_FORM_ID . 'FAIL');
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

    /**
     * @covers  ::getSubmissions
     */
    public function testGetSubmissionsDefaultIdeal() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $form = $wrapper->getFormDetails(GET_SUBMISSIONS_FORM);
        $submissionCount = $form->submissions > 25 ? 25 : $form->submissions;

        $submissions = $wrapper->getSubmissions(GET_SUBMISSIONS_FORM);
        $this->assertEquals(count($submissions), $submissionCount);
    }

    /**
     * @covers  ::getSubmissions
     */
    public function testGetSubmissionsSearchIdeal() {
        global $searchFields, $searchValues;
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $form = $wrapper->getFormDetails(GET_SUBMISSIONS_FORM);
        $submissionCount = $form->submissions > 25 ? 25 : $form->submissions;

        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', $searchFields, $searchValues
        );

        $this->assertLessThan($submissionCount, count($submissions));
        $this->assertGreaterThan(0, count($submissions));
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Form ID must be numeric
     */
    public function testGetSubmissionNonNumericFormId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(GET_SUBMISSIONS_FORM . 'FAIL');
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Invalid value for minTime parameter
     */
    public function testGetSubmissionsBadMinTime() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', 'FAIL'
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Invalid value for maxTime parameter
     */
    public function testGetSubmissionsBadMaxTime() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', 'FAIL'
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    You must have a one to one relationship
     * between field ids and field values
     */
    public function testGetSubmissionsSearchMismatch() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(
            1,
            2,
            3,
        );
        $fieldValues = array(
            'test-1',
            'test-2',
        );

        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', $fieldIds, $fieldValues
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    You must have a one to one relationship
     * between field ids and field values
     */
    public function testGetSubmissionsSearchMismatchReversed() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(
            1,
            2,
        );
        $fieldValues = array(
            'test-1',
            'test-2',
            'test-3',
        );

        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', $fieldIds, $fieldValues
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Field IDs must be numeric
     */
    public function testGetSubmissionsNonNumericFieldIds() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(
            1,
            'fail',
        );
        $fieldValues = array(
            'test-1',
            'test-2',
        );

        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', $fieldIds, $fieldValues
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    The perPage value must be numeric
     */
    public function testGetSubmissionsNonNumericPerPage() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 1, 'fail'
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    You can only retrieve a minimum of 1 and
     * maximum of 100 submissions per request
     */
    public function testGetSubmissionsZeroPerPage() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 1, 0
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    You can only retrieve a minimum of 1 and
     * maximum of 100 submissions per request
     */
    public function testGetSubmissionsAboveOneHundredPerPage() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 1, 101
        );
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    The pageNumber value must be numeric
     */
    public function testGetSubmissionsNonNumericPageNumber() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 'fail'
        );
    }

    /**
     * @covers                      ::getSubmissions
     */
    public function testGetSubmissionsPageNumberWorking() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissionsPage1 = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 1, 1
        );
        $submissionsPage2 = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 2, 1
        );

        $this->assertNotEquals($submissionsPage1, $submissionsPage2);
    }

    /**
     * @covers                      ::getSubmissions
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    The sort parameter must be ASC or DESC
     */
    public function testGetSubmissionsInvalidSort() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 1, 100, 'fail'
        );
    }

    /**
     * @covers                      ::getSubmissions
     */
    public function testGetSubmissionsNoData() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(GET_SUBMISSIONS_FORM);

        $submission = $submissions[0];

        $this->assertFalse(isset($submission->data));
    }

    /**
     * @covers                      ::getSubmissions
     */
    public function testGetSubmissionsWithData() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            GET_SUBMISSIONS_FORM, '', '', '', array(), array(), 1, 100, 'DESC', true
        );

        $submission = $submissions[0];

        $this->assertTrue(isset($submission->data));
    }

    /**
     * @covers  ::submitForm
     */
    public function testSubmitFormIdeal() {
        global $submitFormFieldIds, $submitFormFieldValues;
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->submitForm(
            SUBMIT_FORM_ID, $submitFormFieldIds, $submitFormFieldValues
        );

        $this->assertEquals($response->form, SUBMIT_FORM_ID);

        foreach ($response->data as $submissionData) {
            $fieldCount = count($submitFormFieldIds);

            for ($i = 0; $i < $fieldCount; $i++) {
                if ($submissionData->field == $submitFormFieldIds[$i]) {
                    // Handle Checking Data With Subfields
                    if (is_array($submitFormFieldValues[$i])) {
                        $formattedArray = array();
                        $rows = explode("\n", $submissionData->value);

                        foreach ($rows as $row) {
                            $keyValuePair = explode(' = ', $row);

                            if (empty($keyValuePair[0])) {
                                continue;
                            }

                            $formattedArray[$keyValuePair[0]] = $keyValuePair[1];
                        }

                        $this->assertEquals($formattedArray, $submitFormFieldValues[$i]);
                    } else {
                        $this->assertEquals($submissionData->value,
                            $submitFormFieldValues[$i]
                        );
                    }
                }
            }
        }
    }

    /**
     * @covers                      ::submitForm
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Form ID must be numeric
     */
    public function testSubmitFormNonNumericFormId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->submitForm(SUBMIT_FORM_ID . 'FAIL');
    }

    /**
     * @covers                      ::submitForm
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    You must use a valid Date/Time string formatted
     *  in YYYY-MM-DD HH:MM:SS
     */
    public function testSubmitFormBadTimestamp() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->submitForm(
            SUBMIT_FORM_ID,
            array(),
            array(),
            'this is not a valid date/time string'
        );
    }

    /**
     * @covers                      ::submitForm
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    There must be a one-to-one relationship between
     *  Field IDs and their values
     */
    public function testSubmitFormDataArrayMismatch() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(1,2,3);
        $fieldValues = array('a', 'b');
        $response = $wrapper->submitForm(
            SUBMIT_FORM_ID,
            $fieldIds,
            $fieldValues
        );
    }

    /**
     * @covers                      ::submitForm
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    There must be a one-to-one relationship between
     *  Field IDs and their values
     */
    public function testSubmitFormDataArrayMismatchReversed() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(1,2);
        $fieldValues = array('a', 'b', 'c');
        $response = $wrapper->submitForm(
            SUBMIT_FORM_ID,
            $fieldIds,
            $fieldValues
        );
    }

    /**
     * @covers                      ::submitForm
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Field IDs must be numeric
     */
    public function testSubmitFormNonNumericFieldId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array('fail');
        $fieldValues = array('a');
        $response = $wrapper->submitForm(
            SUBMIT_FORM_ID,
            $fieldIds,
            $fieldValues
        );
    }

    /**
     * @covers                      ::submitForm
     */
    public function testSubmitFormUploadsIdeal() {
        global $uploadFieldIds, $uploadFieldValues;

        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = $uploadFieldIds;
        $fieldValues = $uploadFieldValues;

        $fieldValueCount = count($fieldValues);

        for ($i = 0; $i < $fieldValueCount; $i++) {
            $base64 = base64_encode($fieldValues[$i][0]);
            $fieldValues[$i] = $fieldValues[$i][1] . ';' . $base64;
        }

        $response = $wrapper->submitForm(
            UPLOAD_FORM_ID,
            $fieldIds,
            $fieldValues
        );

        $this->assertFalse(empty($response->data));

        foreach ($response->data as $submissionData) {
            for ($i = 0; $i < $fieldValueCount; $i++) {
                $this->assertEquals($submissionData->field, $fieldIds[$i]);
                $this->assertFalse(empty($submissionData->value));
            }
        }
    }

    /**
     * @covers  ::getSubmissionDetails
     */
    public function testGetSubmissionDetailsIdeal() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submission = $wrapper->getSubmissionDetails(SUBMISSION_DETAILS_ID);
        $this->assertEquals($submission->form, SUBMISSION_DETAILS_FORM_ID);
    }

    /**
     * @covers                      ::getSubmissionDetails
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Submission ID must be numeric
     */
    public function testGetSubmissionDetailsNonNumericId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submission = $wrapper->getSubmissionDetails(SUBMISSION_DETAILS_ID . 'FAIL');
    }

    /**
     * @covers  ::editSubmissionData
     */
    public function testEditSubmissionDataIdeal() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $value = 'test' . time();
        $arrayValue = array(
            'first' =>  'test' . time() . '-first',
            'last'  =>  'test' . time() . '-last',
        );
        $editSubmissionId = $this->getEditableSubmissionId();
        $response = $wrapper->editSubmissionData(
            $editSubmissionId,
            array(EDIT_SUBMISSION_FIELD_ID, EDIT_SUBMISSION_ARRAY_FIELD_ID),
            array($value, $arrayValue)
        );

        $this->assertEquals($response->success, 1);
        $this->assertEquals($response->id, $editSubmissionId);

        $submission = $wrapper->getSubmissionDetails($editSubmissionId);

        foreach ($submission->data as $submissionData) {
            if ($submissionData->field === EDIT_SUBMISSION_FIELD_ID) {
                $this->assertEquals($submissionData->value === $value);
            } elseif ($submissionData->field === EDIT_SUBMISSION_ARRAY_FIELD_ID) {
                $this->assertEquals($submissionData->value === $arrayValue);
            }
        }
    }

    /**
     * @covers                      ::editSubmissionData
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Submission ID must be numeric
     */
    public function testEditSubmissionDataNonNumericId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->editSubmissionData(EDIT_SUBMISSION_ID . 'FAIL');
    }

    /**
     * @covers                      ::editSubmissionData
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    You must use a valid Date/Time string formatted
     *  in YYYY-MM-DD HH:MM:SS'
     */
    public function testEditSubmissionDataBadTimestamp() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->editSubmissionData(
            EDIT_SUBMISSION_ID,
            array(),
            array(),
            'Bad Date String'
        );
    }

    /**
     * @covers                      ::editSubmissionData
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    There must be a one-to-one relationship
     *  between Field IDs and their values
     */
    public function testEditSubmissionDataArrayCountMismatch() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(1,2,3);
        $fieldValues = array('test-1', 'test-2');
        $response = $wrapper->editSubmissionData(
            EDIT_SUBMISSION_ID,
            $fieldIds,
            $fieldValues
        );
    }

    /**
     * @covers                      ::editSubmissionData
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    There must be a one-to-one relationship
     *  between Field IDs and their values
     */
    public function testEditSubmissionDataArrayCountMismatchReversed() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array(1,2,);
        $fieldValues = array('test-1', 'test-2', 'test-3');
        $response = $wrapper->editSubmissionData(
            EDIT_SUBMISSION_ID,
            $fieldIds,
            $fieldValues
        );
    }

    /**
     * @covers                      ::editSubmissionData
     *
     * @expectedException           Exception
     * @expectedExceptionMessage    Field IDs must be numeric
     */
    public function testEditSubmissionDataNonNumericFieldIds() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldIds = array('fail');
        $fieldValues = array('test-1');
        $response = $wrapper->editSubmissionData(
            EDIT_SUBMISSION_ID,
            $fieldIds,
            $fieldValues
        );
    }

    /**
     * @covers  ::deleteSubmission
     */
    public function testDeleteSubmissionIdeal() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissionToDelete = $this->getEditableSubmissionId();
        $response = $wrapper->deleteSubmission($submissionToDelete);

        $this->assertEquals($response->success, 1);
        $this->assertEquals($response->id, $submissionToDelete);
    }

    /**
     * @covers                      ::deleteSubmission
     *
     * @expectedException           Exception
     * @expectedException           Submission ID must be numeric
     */
    public function testDeleteSubmissionNonNumericSubmissionId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $response = $wrapper->deleteSubmission('this is not a valid id');
    }

    /**
     * @covers  ::createField
     */
    public function testCreateFieldIdealSimplest() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $fieldName = 'Test Field ' . time();
        $field = $wrapper->createField(CREATE_FIELD_FORM_ID, 'text', $fieldName);
        $this->assertEquals($field->label, $fieldName);
        $this->assertEquals($field->type, 'text');
    }

    /**
     * @covers  ::createField
     */
    public function testCreateFieldIdealEmbedCode() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $attributes = array(
            'text'  =>  '<script type="text/javascript">alert(\'Woop there it is \');</script>',
        );
        $field = $wrapper->createField(CREATE_FIELD_FORM_ID, 'embed', '', false,
            '', false, $attributes
        );

        $this->assertEquals($field->type, 'embed');
    }

    /**
     * Utility method to get a editable submission id
     *
     * @return  int $submissions[0]->id The id of the first submission found
     */
    private function getEditableSubmissionId() {
        $wrapper = new FormstackApi(ACCESS_TOKEN);
        $submissions = $wrapper->getSubmissions(
            DELETE_SUBMISSION_FORM,
            '',
            '',
            '',
            array(),
            array(),
            1,
            100,
            'ASC'
        );

        return $submissions[0]->id;
    }
}
