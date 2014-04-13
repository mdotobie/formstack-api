<?php
/**
 * Copyright (c) 2014 Formstack, LLC
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright 2014 Formstack, LLC
 * @license   http://www.opensource.org/licenses/mit-license.php
 * @link      http://www.formstack.com/developers
 */

class FormstackApi {
    private $apiUrl = 'https://www.formstack.com/api/v2/';
    private $accessToken = '';
    public  $finalized = true;

    public function __construct($accessToken) {
        $this->accessToken = $accessToken;
    }

    /**
     * Get a list of the forms in your account
     *
     * @link    https://www.formstack.com/developers/api/resources/form#form_GET
     *
     * @param   bool    $folderOrganized    Flag to determine whether response
     *  should be structured in Folders
     *
     * @return  array   $response->forms    Array of all Forms or Array of Folders
     */
    public function getForms($folderOrganized = false) {
        $arguments = array(
            'folders'   =>  $folderOrganized ? 1 : 0,
        );

        $responseJson = $this->request('form.json', 'GET', $arguments);
        $response = json_decode($responseJson);

        if ($folderOrganized) {
            // Folders are returned as properties of the response->forms object
            // Converting response->forms to an array to be similar in behavior
            // to when there are no folders
            $response->forms = (array) $response->forms;
        }

        return $response->forms;
    }

    /**
     * Get the detailed information of a specific form
     *
     * @link    https://www.formstack.com/developers/api/resources/form#form/:id_GET
     *
     * @param   int     $formId     The ID of the form to look up
     *
     * @throws  Exception           If the Form ID is not numeric
     *
     * @return  object  $response   A \stdClass representing all of the Form's data
     */
    public function getFormDetails($formId) {
        if (!is_numeric($formId)) {
            throw new Exception('Form ID must be numeric');
        }

        $responseJson = $this->request('form/' . $formId, 'GET');
        $response = json_decode($responseJson);

        return $response;
    }

    /**
     * Create a copy of a form in your account.
     *
     * @link    https://www.formstack.com/developers/api/resources/form#form/:id/copy_POST
     *
     * @param   int     $formId     The ID of the form to copy
     *
     * @throws  Exception           If the Form ID is not numeric
     *
     * @return  object  $copiedForm A \stdClass representing all of the copy's data
     */
    public function copyForm($formId) {
        if (!is_numeric($formId)) {
            throw new Exception('Form ID must be numeric');
        }

        $responseJson = $this->request('form/' . $formId . '/copy', 'POST');
        $copiedForm = json_decode($responseJson);

        return $copiedForm;
    }

    /**
     * Get all submissions for a specific form
     *
     * @link    https://www.formstack.com/developers/api/resources/submission#form/:id/submission_GET
     *
     * @param   int     $formId             The ID of the form to retrieve submissions for
     * @param   string  $encryptionPassword The encryption password (if applicable)
     * @param   string  $minTime            Date/Time string for start time in EST to group submissions
     * @param   string  $maxTime            Date/Time string for end time in EST to group submissions
     * @param   array   $searchFieldIds     Array of Field IDs to base searching around
     * @param   array   $searchFieldValues  Array of values related to IDs in searchFieldIds
     * @param   int     $pageNumber         Page of submissions to collect from
     * @param   int     $perPage            Number of submissions to retrieve per request
     * @param   string  $sort               Sort direction ('DESC or 'ASC')
     * @param   bool    $data               Whether to include submission data in request
     * @param   bool    $expandData         Whether to include extra data formatting for included data
     *
     * @throws  Exception                   If provided Form ID is not numeric
     * @throws  Exception                   If invalid Date/Time String provided for minTime
     * @throws  Exception                   If invalid Date/Time String provided for maxTime
     * @throws  Exception                   If number of searchIds and searchValues are not identical
     * @throws  Exception                   If pageNumber is not numeric
     * @throws  Exception                   If perPage is not nmueric
     * @throws  Exception                   If perPage is out of bounds (less than 1 or greater than 100)
     * @throws  Exception                   If sort is not 'ASC' or 'DESC'
     * @throws  Exception                   If any searchFieldIds are not numeric
     *
     * @return  array   $submissions        All retrieved submissions for the given Form
     */
    public function getSubmissions($formId, $encryptionPassword = '',
        $minTime = '', $maxTime = '', $searchFieldIds = array(),
        $searchFieldValues = array(), $pageNumber = 1, $perPage = 25, $sort = 'DESC',
        $data = false, $expandData = false) {

        if (!is_numeric($formId)) {
            throw new Exception('Form ID must be numeric');
        }

        $endpoint = 'form/' . $formId . '/submission.json';

        if (!empty($minTime) && strtotime($minTime) === false) {
            throw new Exception('Invalid value for minTime parameter');
        } elseif (!empty($maxTime) && strtotime($maxTime) === false) {
            throw new Exception('Invalid value for maxTime parameter');
        }

        if (count($searchFieldIds) !== count($searchFieldValues)) {
            throw new Exception('You must have a one to one relationship between '
                . 'field ids and field values'
            );
        }

        if (!is_numeric($pageNumber)) {
            throw new Exception('The pageNumber value must be numeric');
        }

        if (!is_numeric($perPage)) {
            throw new Exception('The perPage value must be numeric');
        } elseif ($perPage > 100 || $perPage <= 0) {
            throw new Exception('You can only retrieve a minimum of 1 and '
                . 'maximum of 100 submissions per request'
            );
        }

        $sort = strtoupper($sort);

        if ($sort !== 'ASC' && $sort !== 'DESC') {
            throw new Exception('The sort parameter must be ASC or DESC');
        }

        $arguments = array(
            'encryption_password'   =>  $encryptionPassword,
            'min_time'              =>  $minTime,
            'max_time'              =>  $maxTime,
            'page_number'           =>  $pageNumber,
            'per_page'              =>  $perPage,
            'sort'                  =>  $sort,
            'data'                  =>  $data,
            'expand_data'           =>  $expandData,
        );

        // Clear out empty values
        $argumentCount = count($arguments);

        for ($i = 0; $i < $argumentCount; $i++) {
            if (empty($arguments[$i])) {
                unset($arguments[$i]);
            }
        }

        // Add Search Arguments
        $fieldIdCount = count($searchFieldIds);

        for ($i = 0; $i < $fieldIdCount; $i++) {
            if (!is_numeric($searchFieldIds[$i])) {
                throw new Exception('Field IDs must be numeric only');
            }

            $arguments['search_field_' . $i] = $searchFieldIds[$i];
            $arguments['search_value_' . $i] = $searchFieldValues[$i];
        }

        $responseJson = $this->request($endpoint, 'GET', $arguments);
        $response = json_decode($responseJson);

        return $response->submissions;
    }

    /**
     * Create a new Submission for the specified Form
     *
     * @link    https://www.formstack.com/developers/api/resources/submission#form/:id/submission_POST
     *
     * @param   int     $formId         The ID of the form to submit to
     * @param   array   $fieldIds       Array of field ids to submit data for
     * @param   array   $fieldValues    Array of field values to submit data associated with $fieldIds
     * @param   string  $timestamp      String representation of YYYY-MM-DD HH:MM:SS time that should be recorded
     * @param   string  $userAgent      Browser user agent value that should be recorded
     * @param   string  $ipAddress      IP Address that should be recorded
     * @param   string  $paymentStatus  Status of payment integration(s) (if applicable)
     * @param   bool    $read           Flag (true or false) indicating whether the submission was read
     *
     * @throws  Exception               If a non-numeric Form ID was provided
     * @throws  Exception               If an invalid Date/Time string was provided for $timestamp
     * @throws  Exception               If the count of Field IDs and Field Values does not match
     * @throws  Exception               If any of the Field IDs are not numeric
     *
     * @return  object  $response       \stdClass representation of Submission response
     */
    public function submitForm($formId, $fieldIds = array(), $fieldValues = array(),
        $timestamp = '', $userAgent = '', $ipAddress = '', $paymentStatus = '', $read = false) {

        if (!is_numeric($formId)) {
            throw new Exception('Form ID must be numeric');
        }

        if (!empty($timestamp) && strtotime($timestamp) === false) {
            throw new Exception('You must use a valid Date/Time string formatted '
                . 'in YYYY-MM-DD HH:MM:SS'
            );
        }

        $arguments = array(
            'timestamp'         =>  $timestamp,
            'user_agent'        =>  $userAgent,
            'remote_addr'       =>  $ipAddress,
            'payment_status'    =>  $paymentStatus,
            'read'              =>  $read ? 1 : 0,
        );

        $argumentCount = count($arguments);

        // Remove empty arguments to avoid overwriting existing data
        for ($i = 0; $i < $argumentCount; $i++) {
            if (empty($arguments[$i])) {
                unset($arguments[$i]);
            }
        }

        if (!empty($fieldIds)) {
            $fieldIdCount = count($fieldIds);

            if ($fieldIdCount !== count($fieldValues)) {
                throw new Exception('There must be a one-to-one relationship between '
                    . 'Field IDs and their values'
                );
            }

            for ($i = 0; $i < $fieldIdCount; $i++) {
                if (!is_numeric($fieldIds[$i])) {
                    throw new Exception('Field IDs must be numeric');
                }

                $arguments['field_' . $fieldIds[$i]] = $fieldValues[$i];
            }
        }

        $responseJson = $this->request(
            'form/' . $formId . '/submission.json',
            'POST',
            $arguments
        );
        $response = json_decode($responseJson);

        return $response;
    }

    /**
     * Get the details of a specific submission
     *
     * @link    https://www.formstack.com/developers/api/resources/submission#submission/:id_GET
     *
     * @param   int     $submissionId       The ID of the submission to get data for
     * @param   string  $encryptionPassword The encryption password on the form (if applicable)
     *
     * @throws  Exception                   If the Submission ID is not numeric
     *
     * @return  object  $submission         \stdClass representation of the Submission Data
     */
    public function getSubmissionDetails($submissionId, $encryptionPassword = '') {
        if (!is_numeric($submissionId)) {
            throw new Exception('Submission ID must be numeric');
        }

        $arguments = array();

        if (!empty($encryptionPassword)) {
            $arguments['encryption_password'] = $encryptionPassword;
        }

        $responseJson = $this->request(
            'submission/' . $submissionId . '.json',
            'GET',
            $arguments
        );

        $submission = json_decode($responseJson);

        return $submission;
    }

    /**
     * Update the specified submission
     *
     * @link    https://www.formstack.com/developers/api/resources/submission#submission/:id_PUT
     *
     * @param   int     $submissionId   The Submission to update
     * @param   array   $fieldIds       An array of all field IDs to update values of
     * @param   array   $fieldValues    An array of all values associated with IDs in $fieldIds
     * @param   string  $timestamp      The time that should be recorded for the submission (YYYY-MM-DD HH:MM:SS)
     * @param   string  $userAgent      The Browser user agent to be recorded for the submission
     * @param   string  $ipAddress      The IP address that should be recorded for the submission
     * @param   string  $paymentStatus  Status of payment integration (if applicable)
     * @param   bool    $read           Flag indicating the submission being read or unread
     *
     * @throws  Exception               If submission ID is not numeric
     * @throws  Exception               If an invalid Date/Time string is used for timestamp
     * @throws  Exception               If there's differing numbers of FieldIds and FieldValues
     * @throws  Exception               If any Field ID provided is non-numeric
     *
     * @return  object  $response       \stdClass representation of API response
     */
    public function editSubmissionData($submissionId, $fieldIds = array(),
        $fieldValues = array(), $timestamp = '', $userAgent = '', $ipAddress = '',
        $paymentStatus = '', $read = false) {

        if (!is_numeric($submissionId)) {
            throw new Exception('Submission ID must be numeric');
        }

        if (!empty($timestamp) && strtotime($timestamp) === false) {
            throw new Exception('You must use a valid Date/Time string formatted '
                . 'in YYYY-MM-DD HH:MM:SS'
            );
        }

        $arguments = array(
            'timestamp'         =>  $timestamp,
            'user_agent'        =>  $userAgent,
            'remote_addr'       =>  $ipAddress,
            'payment_status'    =>  $paymentStatus,
            'read'              =>  $read ? 1 : 0,
        );

        $argumentCount = count($arguments);

        // Remove empty arguments to avoid overwriting existing data
        for ($i = 0; $i < $argumentCount; $i++) {
            if (empty($arguments[$i])) {
                unset($arguments[$i]);
            }
        }

        if (!empty($fieldIds)) {
            $fieldIdCount = count($fieldIds);

            if ($fieldIdCount !== count($fieldValues)) {
                throw new Exception('There must be a one-to-one relationship between '
                    . 'Field IDs and their values'
                );
            }

            for ($i = 0; $i < $fieldIdCount; $i++) {
                if (!is_numeric($fieldIds[$i])) {
                    throw new Exception('Field IDs must be numeric');
                }

                $arguments['field_' . $fieldIds[$i]] = $fieldValues[$i];
            }
        }

        $responseJson = $this->request(
            'submission/' . $submissionId . '.json',
            'PUT',
            $arguments
        );
        $response = json_decode($responseJson);

        return $response;
    }

    /**
     * Delete the specified submission
     *
     * @link    https://www.formstack.com/developers/api/resources/submission#submission/:id_DELETE
     *
     * @param   int     $submissionId   The ID of the Submission to delete
     *
     * @throws  Exception               If the Submission ID is not numeric
     *
     * @return  object  $response       \stdClass representation of the API response
     */
    public function deleteSubmission($submissionId) {
        if (!is_numeric($submissionId)) {
            throw new Exception('Submission ID must be numeric');
        }

        $responseJson = $this->request('submission/' . $submissionId, 'DELETE');
        $response = json_decode($responseJson);

        return $response;
    }

    /**
     * Helper method to make all requests to Formstack API
     *
     * @param   string      $endpoint   The endpoint to make requests to
     * @param   string      $verb       String representation of HTTP verb to perform
     * @param   array       $arguments  Array of all request arguments to use
     *
     * @throws  Exception               if no endpoint is specified
     * @throws  Exception               if an invalid verb is specified
     * @throws  Exception               if the HTTP request fails
     *
     * @return  string                  JSON response from request
     */
    public function request($endpoint, $verb = 'GET', $arguments = array()) {
        if (empty($endpoint)) {
            throw new Exception('You must include an enpoint to request');
        }

        $validVerbs = array(
            'GET',
            'PUT',
            'POST',
            'DELETE',
        );

        // Ensure HTTP verb is declared properly
        $verb = strtoupper($verb);
        $url = $this->apiUrl . $endpoint;

        if (!in_array($verb, $validVerbs)) {
            throw new Exception('Your requests must be performed with one of the '
                . 'following verbs: ' . implode(', ', $validVerbs) . '.'
            );
        }

        $ch = curl_init();

        if (!empty($arguments)) {
            if ($verb === 'GET') {
                $url .= '?' . http_build_query($arguments);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arguments));
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->accessToken
            )
        );

        if ($verb === 'POST' || $verb === 'PUT') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if ($verb === 'PUT' || $verb === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        }

        $result = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrorCode = curl_errno($ch);
        $curlErrorMessage = curl_error($ch);

        curl_close($ch);

        if ($httpStatus < 200 || $httpStatus >= 300) {
            if ($this->finalized) {
                throw new Exception('Request failed. Exception code contains HTTP Status.', $httpStatus);
            }

            print 'Server Response: ' . print_r($result, true) . "\n\n"
                . 'HTTP Status Code: ' . $httpStatus . "\n\n" . 'Curl Error Code: '
                . print_r($curlErrorCode, true) . "\n\n" . 'Curl Error Message: '
                . print_r($curlErrorMessage, true);
        }

        return $result;
    }
}
