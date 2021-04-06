<?php

/**
 * Components View html page
 * 
 * PHP version 5.6.x | 7.x | 8.x
 * 
 * @category  Components
 * @package   Joomla
 * @author    Pejman Kheyri <pejmankheyri@gmail.com>
 * @copyright 2021 All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// Load tooltip behavior
JHtml::_('behavior.tooltip');

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration(
    'Joomla.submitbutton = function(task)
	{
		if (task == "ipesms.cancel" || document.formvalidator.isValid(document.id("ipesms-form")))
		{
			Joomla.submitform(task, document.getElementById("ipesms-form"));
		}
	}'
);

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>
<form action="<?php echo JRoute::_('index.php?option=com_smsnotification'); ?>" method="post" name="adminForm" id="ipesms-form" class="form-validate">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <div class="control-group">
            <div class="control-label">
                <?php echo JText::_('COM_IPESMS_FIELD_BALANCE_LABEL');?>
            </div>
            <div class="controls">
                <?php echo $this->balance." ".JText::_('COM_SMSNOTIFICATION_MESSAGE'); ?>
            </div>
        </div>
        <?php
        foreach ($fieldsets as $fieldset) {
            echo $this->form->getControlGroups($fieldset->name);
        }
        ?>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
