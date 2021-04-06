<?php

/**
 * Components Model File For SMSIR Bulk Gateway
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

jimport('joomla.application.component.helper');

/**
 * Model of SmsIr Bulk Gateway Class
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */
class SMSNotificationModelSMSNotification extends JModelAdmin
{
    /**
     * Gets API Customer Club Add Contact And Send Url.
     *
     * @return string Indicates the Url
     */
    protected function getAPICustomerClubAddAndSendUrl()
    {
        return "api/CustomerClub/AddContactAndSend";
    }

    /**
     * Gets API Customer Club Send To Categories Url.
     *
     * @return string Indicates the Url
     */
    protected function getAPICustomerClubSendToCategoriesUrl()
    {
        return "api/CustomerClub/SendToCategories";
    }

    /**
     * Gets API credit Url.
     *
     * @return string Indicates the Url
     */
    protected function getAPIcreditUrl()
    {
        return "api/credit";
    }

    /**
     * Gets API Message Send Url.
     *
     * @return string Indicates the Url
     */
    protected function getAPIMessageSendUrl()
    {
        return "api/MessageSend";
    }
    
    /**
     * Gets Api Token Url.
     *
     * @return string Indicates the Url
     */
    protected function getApiTokenUrl()
    {
        return "api/Token";
    }

    /**
     * Method to get the record form
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not
     * 
     * @return mixed A JForm object on success, false on failure
     */
    public function getForm($data=array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_smsnotification.smsnotification', 'smsnotification', array('control' => 'jform', 'load_data' => $loadData));
        if (!$form) {
            return false;
        } else {
            return $form;
        }
    }
    
    /**
     * Get Credit.
     *
     * @return boolean Indicates the Credit
     */
    public function getBalance()
    {
        $params = JComponentHelper::getParams('com_smsnotification');
        $username = $params->get('ipesms_username');
        $password = $params->get('ipesms_password');
        $apidomain = $params->get('ipesms_apidomain');

        if (!$this->validateIPESMS($username, $password)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SMSNOTIFICATION_CONFIG_ERROR'), 'warning');
            return 0;
        }

        $token = $this->_getToken($username, $password);

        $result = false;
        if ($token != false) {

            $url = $apidomain.$this->getAPIcreditUrl();
            $GetCredit = $this->_executeCredit($url, $token);

            $object = json_decode($GetCredit);

            if (is_object($object)) {
                if ($object->IsSuccessful == true) {
                    $result = $object->Credit;
                } else {
                    $result = $object->Message;
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Get Users.
     *
     * @return boolean Indicates the Credit
     */
    public function getUsers()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('a.username', 'b.profile_value', 'b.profile_key', 'a.id', 'b.user_id')))
            ->from($db->quoteName('#__users', 'a'))
            ->join('LEFT', $db->quoteName('#__user_profiles', 'b') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.user_id') . ')')
            ->where($db->quoteName('b.profile_key') . ' LIKE ' . $db->quote('smsnotificationprofile.%'))
            ->order('ordering');
        $db->setQuery($query);
        $results = $db->loadRowList();
        $returnArray = array();
        foreach ($results as $result) {
            $name = $result[0];
            $phoneNumber = $result[1];
            $returnArray[] = array($name, $phoneNumber);
        }
        return $returnArray;
    }
    
    /**
     * Send SMS.
     * 
     * @param array  $phone_numbers phone numbers
     * @param string $message       message string
     * @param string $senderID      message sender id
     *
     * @return boolean
     */
    public function sendIPESMS($phone_numbers, $message, $senderID = '')
    {
        $params = JComponentHelper::getParams('com_smsnotification');
        $apidomain = $params->get('ipesms_apidomain');
        $username = $params->get('ipesms_username');
        $password = $params->get('ipesms_password');
        $linenumber = $params->get('ipesms_linenumber');
        $iscustomerclub = $params->get('ipesms_iscustomerclub');

        if (!$this->validateIPESMS($username, $password)) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_SMSNOTIFICATION_CONFIG_ERROR'), 'error');
        }
        
        foreach ($phone_numbers as $key=>$value) {
            if (($this->isMobile($value)) || ($this->isMobileWithz($value))) {
                $number[] = doubleval($value);
            }
        }
        @$numbers = array_unique($number);

        if (is_array($numbers) && $numbers) {
            foreach ($numbers as $key => $value) {
                $Messages[] = $message;
            }
        }

        $SendDateTime = date("Y-m-d")."T".date("H:i:s");

        date_default_timezone_set('Asia/Tehran');

        if ((isset($iscustomerclub)) && ($iscustomerclub == 1)) {

            foreach ($numbers as $num_keys => $num_vals) {
                $contacts[] = array(
                    "Prefix" => "",
                    "FirstName" => "" ,
                    "LastName" => "",
                    "Mobile" => $num_vals,
                    "BirthDay" => "",
                    "CategoryId" => "",
                    "MessageText" => $message
                );
            }

            $CustomerClubInsertAndSendMessage = $this->customerClubInsertAndSendMessage($contacts);

            if ($CustomerClubInsertAndSendMessage == true) {
                return true;
            } else {
                return false;
            }
        } else {

            $SendMessage = $this->sendMessage($numbers, $Messages, $SendDateTime);

            if ($SendMessage == true) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Validate sms.
     * 
     * @param string $username user name
     * @param string $password password
     *
     * @return boolean
     */
    public function validateIPESMS($username, $password)
    {
        if ($username && $password) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Customer Club Send To Categories.
     *
     * @param Messages[] $Messages array structure of messages
     * 
     * @return boolean Indicates the sent sms result
     */
    public function sendSMStoCustomerclubContacts($Messages)
    {
        $params = JComponentHelper::getParams('com_smsnotification');
        $username = $params->get('ipesms_username');
        $password = $params->get('ipesms_password');
        $apidomain = $params->get('ipesms_apidomain');

        $contactsCustomerClubCategoryIds = array();
        $token = $this->_getToken($username, $password);
        if ($token != false) {
            $postData = array(
                'Messages' => $Messages,
                'contactsCustomerClubCategoryIds' => $contactsCustomerClubCategoryIds,
                'SendDateTime' => '',
                'CanContinueInCaseOfError' => 'false'
            );

            $url = $apidomain.$this->getAPICustomerClubSendToCategoriesUrl();
            $CustomerClubSendToCategories = $this->_execute($postData, $url, $token);
            $object = json_decode($CustomerClubSendToCategories);

            if (is_object($object)) {
                if ($object->IsSuccessful == true) {
                    return true;
                } else {
                    return false;
                }                
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Send sms.
     *
     * @param MobileNumbers[] $MobileNumbers array structure of mobile numbers
     * @param Messages[]      $Messages      array structure of messages
     * @param string          $SendDateTime  Send Date Time
     * 
     * @return string Indicates the sent sms result
     */
    public function sendMessage($MobileNumbers, $Messages, $SendDateTime = '')
    {
        $params = JComponentHelper::getParams('com_smsnotification');
        $apidomain = $params->get('ipesms_apidomain');
        $username = $params->get('ipesms_username');
        $password = $params->get('ipesms_password');
        $linenumber = $params->get('ipesms_linenumber');

        $token = $this->_getToken($username, $password);

        $result = false;
        if ($token != false) {
            $postData = array(
                'Messages' => $Messages,
                'MobileNumbers' => $MobileNumbers,
                'LineNumber' => $linenumber,
                'SendDateTime' => $SendDateTime,
                'CanContinueInCaseOfError' => 'false'
            );

            $url = $apidomain.$this->getAPIMessageSendUrl();
            $SendMessage = $this->_execute($postData, $url, $token);
            $object = json_decode($SendMessage);

            if (is_object($object)) {
                if ($object->IsSuccessful == true) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Customer Club Insert And Send Message.
     *
     * @param data[] $data array structure of contacts data
     * 
     * @return string Indicates the sent sms result
     */
    public function customerClubInsertAndSendMessage($data) 
    {
        $params = JComponentHelper::getParams('com_smsnotification');
        $username = $params->get('ipesms_username');
        $password = $params->get('ipesms_password');
        $apidomain = $params->get('ipesms_apidomain');

        $token = $this->_getToken($username, $password);

        $result = false;
        if ($token != false) {
            $postData = $data;

            $url = $apidomain.$this->getAPICustomerClubAddAndSendUrl();
            $CustomerClubInsertAndSendMessage = $this->_execute($postData, $url, $token);
            $object = json_decode($CustomerClubInsertAndSendMessage);

            if (is_object($object)) {
                if ($object->IsSuccessful == true) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    /**
     * Gets token key for all web service requests.
     *
     * @return string Indicates the token key
     */
    private function _getToken()
    {
        $params = JComponentHelper::getParams('com_smsnotification');
        $username = $params->get('ipesms_username');
        $password = $params->get('ipesms_password');
        $apidomain = $params->get('ipesms_apidomain');

        $postData = array(
            'UserApiKey' => $username,
            'SecretKey' => $password,
            'System' => 'joomla_3_v_2_1'
        );
        $postString = json_encode($postData);

        $ch = curl_init($apidomain.$this->getApiTokenUrl());
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json'
            )
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);
        $resp = false;

        if (is_object($response)) {
            @$IsSuccessful = $response->IsSuccessful;
            if ($IsSuccessful == true) {
                @$TokenKey = $response->TokenKey;
                $resp = $TokenKey;
            } else {
                $resp = false;
            }
        }
        return $resp;
    }

    /**
     * Executes the main method.
     *
     * @param postData[] $postData array of json data
     * @param string     $url      url
     * @param string     $token    token string
     * 
     * @return string Indicates the curl execute result
     */
    private function _execute($postData, $url, $token)
    {
        $postString = json_encode($postData);

        $ch = curl_init($url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'x-sms-ir-secure-token: '.$token
            )
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Executes the main method.
     *
     * @param string $url   url
     * @param string $token token string
     *
     * @return string Indicates the curl execute result
     */
    private function _executeCredit($url, $token)
    {
        $ch = curl_init($url);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'x-sms-ir-secure-token: '.$token
            )
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Check if mobile number is valid.
     *
     * @param string $mobile mobile number
     *
     * @return boolean Indicates the mobile validation
     */
    public function isMobile($mobile)
    {
        if (preg_match('/^09(0[1-5]|1[0-9]|3[0-9]|2[0-2]|9[0-1])-?[0-9]{3}-?[0-9]{4}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if mobile with zero number is valid.
     *
     * @param string $mobile mobile with zero number
     * 
     * @return boolean Indicates the mobile with zero validation
     */
    public function isMobileWithz($mobile)
    {
        if (preg_match('/^9(0[1-5]|1[0-9]|3[0-9]|2[0-2]|9[0-1])-?[0-9]{3}-?[0-9]{4}$/', $mobile)) {
            return true;
        } else {
            return false;
        }
    }
}
?>