<?php

/**
 *
 * @package    MUtil
 * @subpackage Controller
 * @author     Jasper van Gestel <jappie@dse.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

/**
 * An extension to Zend Flashmessenger to allow for status updates in a flash message.
 * Each Message will be shown as a seperate message. You can group Messages in one status by passing it as an Array.
 *
 * @package    MUtil
 * @subpackage Controller
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */
class MUtil_Controller_Action_Helper_FlashMessenger extends \Zend_Controller_Action_Helper_FlashMessenger
{
	/**
	 * @var string The default status, if no status has been set.
	 */
	protected $_defaultStatus = 'warning';

	/**
	 * Add a message with a status
	 * @param string|array     $message   The message to add. You can group Messages in one status by passing them as an Array
	 * @param string $status    The status to add to the message, one of: success, info, warning or danger
	 * @param string $namespace The messages namespace
	 */
	public function addMessage($message, $status = null, $namespace = null)
	{
		if (!$status) {
			$status = $this->_defaultStatus;
		}

        $message = array($message, $status);

		parent::addMessage($message, $namespace);

		return $this;
	}

    /**
     * Return all messages in an array without status info.
     */
    public function getMessagesOnly()
    {
    	if ($this->hasMessages()) {
            $messages = $this->getMessages();
        } else {
            $messages = array();
        }

        if ($this->hasCurrentMessages()) {
            $messages = array_merge($messages, $this->getCurrentMessages());
        }

        if (! $messages) {
            return null;
        }

        $output = array();
        foreach ($messages as $message) {
            if (is_array($message)) {
                if ((2 === count($message)) &&
                        isset($message[0], $message[1]) &&
                        is_string($message[1])) {
                    $message = $message[0];
                }
            }
            $output[] = $message;
        }

        return \MUtil_Ra::flatten($output);
    }

    /**
     * Show Available messages in alerts. Bootstrap compatible
     * @return ErrorContainer Html nodes with the Errors.
     */
    public function showMessages()
    {
    	if ($this->hasMessages()) {
            $messages = $this->getMessages();
        } else {
            $messages = array();
        }

        if ($this->hasCurrentMessages()) {
            $messages = array_merge($messages, $this->getCurrentMessages());
        }


        if ($messages) {
            $errorContainer = \MUtil_Html::create()->div(array('class' => 'errors'));
            $errorClose = \MUtil_Html::create()->button(array('type' => 'button','class' => 'close', 'data-dismiss' => 'alert'));
            $errorClose->raw('&times;');

            foreach ($messages as $message) {
                $status = 'warning';

                if (is_array($message)) {
                 	if ((2 === count($message)) &&
                            isset($message[0], $message[1]) &&
                            is_string($message[1])) {
                    	$status  = $message[1];
                    	$message = $message[0];
                    }
                    if (is_array($message)) {
                        // Use array_values to remove string keys (as those are interpreted
                        // as attributes
                    	$message = \MUtil_Html::create()->ul(array_values($message));
                    }
                }

                $errorContainer->div(
                        array('class' => 'alert alert-'.$status, 'role' => 'alert'),
                        $errorClose,
                        $message
                        );
            }

            $this->clearCurrentMessages();

            return $errorContainer;
        }
    }
}
