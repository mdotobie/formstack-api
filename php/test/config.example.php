<?php
/**
 * All of the Constant values in this file are censored or otherwise modified.
 * We've kept values the same where they were the same in our own config file.
 */
    define('ACCESS_TOKEN', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
    define('UNEMPTY_FOLDER_COUNT', 1);
    define('FORM_DETAILS_ID', 0000000000);
    define('FORM_DETAILS_NAME', 'Test');
    define('COPY_FORM_ID', 0000000000);
    define('GET_SUBMISSIONS_FORM', 0000000000);
    define('SUBMISSION_DETAILS_ID', 0000000000);
    define('SUBMISSION_DETAILS_FORM_ID', 0000000000);
    define('EDIT_SUBMISSION_ID', 0000000000);
    define('EDIT_SUBMISSION_FIELD_ID', 0000000000);
    define('EDIT_SUBMISSION_ARRAY_FIELD_ID', 0000000000);
    define('DELETE_SUBMISSION_FORM', 0000000000);
    define('SUBMIT_FORM_ID', DELETE_SUBMISSION_FORM);
    define('CREATE_FIELD_FORM_ID', 0000000000);
    define('UPLOAD_FORM_ID', 00000000000);
    $submitFormFieldIds = array(
        0000000000,
        0000000000,
        0000000000,
    );
    $submitFormFieldValues = array(
        'short-answer-test',
        array(
            'first' =>  'first-test',
            'last'  =>  'last-test',
        ),
        'this is a long answer field',
    );
    $searchFields = array(
        0000000000,
    );
    $searchValues = array(
        '5.00',
    );
    $uploadFieldIds = array(
        0000000000,
    );
    $uploadFieldValues = array(
        array(
            file_get_contents(DIRNAME(__FILE__) . '/fs-logo.png'),
            'fs-logo.png',
        ),
    );

    // We use time-based code in here, so you may need to run the code below if
    // a timezone is not defined elsewhere in your environment
    // date_default_timezone_set('UTC');
