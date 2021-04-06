<?php

/**
 * Components Controller File
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

jimport('joomla.application.component.helper');
jimport('joomla.access.access');
jimport('joomla.user.user');

/**
 * Controller of SMSIR component
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */
class SMSNotificationControllerSMSNotification extends JControllerLegacy
{
    /**
     * Send method for controller
     *
     * @return void
     */
    public function send() 
    {
        // Get the form data
        $formData = new JRegistry($this->input->get('jform', '', 'array'));
        $recipient = $formData->get('recipient', 0);
        switch ($recipient) {
        case "phone_number":
            $this->_sendPhoneNumber($formData);
            break;
        case "contact":
            $this->_sendContact($formData);
            break;
        case "usergroup":
            if (JPluginHelper::isEnabled("user", "smsnotificationprofile")) {
                $this->_sendUsergroup($formData);
            } else {
                $this->setMessage(JText::_('COM_SMSNOTIFICATION_ACTIVATE_USER_PLUGIN'));
                $this->setRedirect('index.php?option=com_smsnotification');
            }
            break;
        case "allcustomerclub":
            $this->_sendToallcustomerclub($formData);
            break;
        }
    }
    
    /**
     * Send method for controller
     *
     * @param string $formData form data message and number
     *
     * @return void
     */
    private function _sendPhoneNumber($formData)
    {
        $message = $formData->get('message', JText::_('COM_SMSNOTIFICATION_NO_MESSAGE'));
        $phoneNumber = $formData->get('to_phone_number');
        
        $returnMessage = "";
        $returnStatus = "message";

        $numbers_array = explode(',', $phoneNumber);

        if (!empty($message)) {
            $return = $this->getModel()->sendIPESMS($numbers_array, $message);
        } else {
            $returnStatus = "error";
        }   

        if (!empty($return)) {
            if ($return == true) {
                $returnMessage = JText::_('COM_SMSNOTIFICATION_ALERT_SUCCESS');
            } else {
                $returnMessage = $return;
                $returnStatus = "error";
            }
        }
        
        $this->setMessage($returnMessage, $returnStatus);
        $this->setRedirect('index.php?option=com_smsnotification');
    }
    
    /**
     * Send contact method for controller
     *
     * @param string $formData form data message and number
     *
     * @return void
     */
    private function _sendContact($formData)
    {
        $message = $formData->get('message', JText::_('COM_SMSNOTIFICATION_NO_MESSAGE'));
        $phoneNumber[] = str_replace('"', "", $formData->get('to_contact'));
        
        $returnMessage = "";
        $returnStatus = "message";
                
        $return = $this->getModel()->sendIPESMS($phoneNumber, $message);
        
        if (!empty($return)) {
            if ($return == true) {
                $returnMessage = JText::_('COM_SMSNOTIFICATION_ALERT_SUCCESS');
            } else {
                $returnMessage = $return;
                $returnStatus = "error";
            }
        }
        
        $this->setMessage($returnMessage, $returnStatus);
        $this->setRedirect('index.php?option=com_smsnotification');
    }

    /**
     * Send to user groups method for controller
     *
     * @param string $formData form data message and number
     *
     * @return void
     */
    private function _sendUsergroup($formData)
    {
        $message = $formData->get('message', JText::_('COM_SMSNOTIFICATION_NO_MESSAGE'));
        $userGroup = $formData->get('to_usergroup');
        $groupUsers = JAccess::getUsersByGroup($userGroup);
        
        $phoneNumbers = array();
        
        $notificationArray = array();
        $returnMessage = "";
        $returnStatus = "message";

        foreach ($groupUsers as $user_id) {
            $user = JFactory::getUser($user_id);
            $profile = JUserHelper::getProfile($user_id);
            if (isset($profile->smsnotificationprofile['phone_number']) && $profile->smsnotificationprofile['phone_number'] != "") {
                $phoneNumbers[] = $profile->smsnotificationprofile['phone_number'];
            }
        }
  
        $return = $this->getModel()->sendIPESMS($phoneNumbers, $message);
        
        if (!empty($return)) {
            if ($return == true) {
                $notification = JText::_('COM_SMSNOTIFICATION_ALERT_SUCCESS');
            } else {
                $notification = $return;
                $returnStatus = "error";
            }
            
            $this->setMessage($notification, $returnStatus);
        }
        
        $this->setRedirect('index.php?option=com_smsnotification');
    }

    /**
     * Send to all customer club method for controller
     *
     * @param string $formData form data message and number
     *
     * @return void
     */    
    private function _sendToallcustomerclub($formData)
    {
        $message = $formData->get('message', JText::_('COM_SMSNOTIFICATION_NO_MESSAGE'));
        
        $returnMessage = "";
        $returnStatus = "message";     
               
        $return = $this->getModel()->sendSMStoCustomerclubContacts($message);
        
        if (!empty($return)) {
            if ($return == true) {
                $returnMessage = JText::_('COM_SMSNOTIFICATION_ALERT_SUCCESS');
            } else {
                $returnMessage = $return;
                $returnStatus = "error";
            }
        }
        
        $this->setMessage($returnMessage, $returnStatus);
        $this->setRedirect('index.php?option=com_smsnotification');
    }
}