<?php

/**
 * Components View class page
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

// import Joomla view library
jimport('joomla.application.component.view');

jimport('joomla.application.component.helper');

/**
 * Modules View class
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */
class SMSNotificationViewSMSNotification extends JViewLegacy
{
    /**
     * SMSIR view display method
     * 
     * @param string $tpl The name of the template file to parse; automatically searches throug the template paths/
     *
     * @return mixed A string if successful, otherwise a JError object.
     */
    function display($tpl = null)
    {
        // Get data from the model
        $form = $this->get('Form');
        $balance = $this->get('Balance');
        $contacts = $this->get('Contacts');
                
        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));
            return false;
        }
        
        
        // Assign data to the view
        $this->form = $form;
        $this->balance = $balance;
        $this->contacts = $contacts;
        
        // Set the toolbar
        $this->addToolBar();
        
        JHtml::_('jquery.framework');
        
        // Display the template
        parent::display($tpl);
    }
    
    /**
     * Setting the toolbar
     * 
     * @return mixed A string if successful, otherwise a JError object.
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_SMSNOTIFICATION_VIEW_SEND_SMS_TITLE'));
        JToolBarHelper::custom('smsnotification.send', 'redo', null, 'COM_IPESMS_TOOLBAR_SEND', false);
        JToolBarHelper::preferences('com_smsnotification');
    }
}
?>