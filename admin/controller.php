<?php

/**
 * Components Main Controller File
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

// NO direct access to this file
defined('_JEXEC') or die;

/**
 * General Controller of SMSIR component
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */
class SMSNotificationController extends JControllerLegacy
{
    /**
     * Display task.
     *
     * @param string $cachable  is display cachable
     * @param string $urlparams url params
     * 
     * @return void Indicates the sent sms result
     */
    function display($cachable = false, $urlparams = false)
    {
        // set default view if not set
        $input = JFactory::getApplication()->input;
        $input->set('view', $input->getCmd('view', 'SMSNotification'));
        
        // call parent behavior
        parent::display($cachable);
    }
}
?>