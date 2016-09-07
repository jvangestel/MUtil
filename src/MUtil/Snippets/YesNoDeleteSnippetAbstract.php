<?php

/**
 *
 * @package    MUtil
 * @subpackage YesNoDeleteSnippet
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Snippets;

/**
 *
 *
 * @package    MUtil
 * @subpackage YesNoDeleteSnippet
 * @copyright  Copyright (c) 2016 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil version 1.8.2 Sep 7, 2016 5:39:53 PM
 */
abstract class YesNoDeleteSnippetAbstract extends \MUtil_Snippets_SnippetAbstract
{
    /**
     * The controller to go to when the user clicks 'No'.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected $abortController;

    /**
     * The action to go to when the user clicks 'No'.
     *
     * If you want to change to another controller you'll have to code it.
     *
     * @var string
     */
    protected $abortAction;

    /**
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @var mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    protected $afterSaveRouteUrl;

    /**
     * Optional class for use on buttons, overruled by $buttonNoClass and $buttonYesClass
     *
     * @var string
     */
    protected $buttonClass;

    /**
     * Optional class for use on No button
     *
     * @var string
     */
    protected $buttonNoClass;

    /**
     * Optional class for use on Yes button
     *
     * @var string
     */
    protected $buttonYesClass;

    /**
     * The request parameter used to store the confirmation
     *
     * @var string Required
     */
    protected $confirmParameter = 'confirmed';

    /**
     * The question to as the user.
     *
     * @var sting Optional
     */
    protected $deleteQuestion;

    /**
     *
     * @var \Zend_Controller_Request_Abstract
     */
    protected $request;

    /**
     * Called after the check that all required registry values
     * have been set correctly has run.
     *
     * @return void
     */
    public function afterRegistry()
    {
        parent::afterRegistry();

        if ($this->buttonClass) {
            if (! $this->buttonNoClass) {
                $this->buttonNoClass = $this->buttonClass;
            }
            if (! $this->buttonYesClass) {
                $this->buttonYesClass = $this->buttonClass;
            }
        }
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil_Html_HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $div = \Mutil_Html::create('p');

        $div->append($this->getQuestion());
        $div->append(' ');
        $div->a(
                array($this->confirmParameter => 1),
                $this->_('Yes'),
                array('class' => $this->buttonYesClass)
                );
        $div->append(' ');
        $div->a(
                array(
                    $this->request->getControllerKey() => $this->abortController ?: $this->request->getControllerName(),
                    $this->request->getActionKey() => $this->abortAction ?: $this->request->getActionName(),
                    ),
                $this->_('No'),
                array('class' => $this->buttonNoClass)
                );

        return $div;
    }

    /**
     * The delete question.
     *
     * @return string
     */
    protected function getQuestion()
    {
        if ($this->deleteQuestion) {
            return $this->deleteQuestion;
        } else {
            return $this->_('Do you really want to do this?');
        }
    }

    /**
     * When hasHtmlOutput() is false a snippet user should check
     * for a redirectRoute.
     *
     * When hasHtmlOutput() is true this functions should not be called.
     *
     * @see \Zend_Controller_Action_Helper_Redirector
     *
     * @return mixed Nothing or either an array or a string that is acceptable for Redector->gotoRoute()
     */
    public function getRedirectRoute()
    {
        return $this->afterSaveRouteUrl;
    }

    /**
     * The place to check if the data set in the snippet is valid
     * to generate the snippet.
     *
     * When invalid data should result in an error, you can throw it
     * here but you can also perform the check in the
     * checkRegistryRequestsAnswers() function from the
     * {@see \MUtil_Registry_TargetInterface}.
     *
     * @return boolean
     */
    public function hasHtmlOutput()
    {
        if ($this->request->getParam($this->confirmParameter)) {
            $this->performAction();

            $redirectRoute = $this->getRedirectRoute();
            return empty($redirectRoute);

        } else {
            return parent::hasHtmlOutput();
        }
    }

    /**
     * Tell what to do and set afterSaveRouteUrl
     */
    abstract protected function performAction();
}
