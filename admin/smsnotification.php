<?php

/**
 * Components Main File
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

// Get an istance of the contorller prefixed by SMSIR
    $controller = JControllerLegacy::getInstance('SMSNotification');

// Perform the Request task and Execute request task
    $controller->execute(JFactory::getApplication()->input->getCmd('task'));

// Redirect if set by the controller
    $controller->redirect();