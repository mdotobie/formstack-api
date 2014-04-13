<?php
/**
 * All of the Constant values in this file are censored or otherwise modified.
 * We've kept values the same where they were the same in our own config file.
 */
    define('ACCESS_TOKEN', 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');
    define('UNEMPTY_FOLDER_COUNT', 1);
    define('FORM_DETAILS_ID', 1234567);
    define('FORM_DETAILS_NAME', 'Test');
    define('COPY_FORM_ID', 1345678);
    define('GET_SUBMISSIONS_FORM', 1456789);
    define('SUBMISSION_DETAILS_ID', 156789123);
    define('SUBMISSION_DETAILS_FORM_ID', 1456789);
    define('EDIT_SUBMISSION_ID', 167891234);
    define('EDIT_SUBMISSION_FIELD_ID', 17891234);
    define('EDIT_SUBMISSION_ARRAY_FIELD_ID', 18912345);
    define('DELETE_SUBMISSION_FORM', 1912345);
    define('SUBMIT_FORM_ID', DELETE_SUBMISSION_FORM);
    $submitFormFieldIds = array(
        17891234,
        18912345,
        11234567
    );
    $submitFormFieldValues = array(
        'short-answer-test',
        array(
            'first' =>  'first-test',
            'last'  =>  'last-test',
        ),
        'this is a long answer field',
    );

    date_default_timezone_set('UTC');
