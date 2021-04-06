<?php

/**
 * Get Contacts List
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

// Load SMS Notification Model
jimport('joomla.application.component.model');

/**
 * Joomla Form Field Contact
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */
class JFormFieldContact extends JFormFieldList
{
    // The field class must know its own type through the variable $type.
    protected $type = 'contact';
    
    /**
     * Gets Option fields.
     *
     * @return array Indicates the array of options
     */
    protected function getOptions()
    {
        $options = array();

        JModelLegacy::addIncludePath(JPATH_SITE . '/administrator/components/com_smsnotification/models');

        $smsNotificationModel = JModelLegacy::getInstance('SMSNotification', 'SMSNotificationModel');

        $users = $smsNotificationModel->getUsers();
        foreach ($users as $user) {
            $name = $user[0];
            $phoneNumber = $user[1];
            $option = JHtml::_('select.option', $phoneNumber, $name);
            $options[] = $option;
        }

        array_unshift($options, JHtml::_('select.option', '0', JText::_('COM_SMSNOTIFICATION_SELECT_USER')));

        return array_merge(parent::getOptions(), $options);
    }
}