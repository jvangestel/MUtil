<?php

/**
 *
 * @package    MUtil
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 * Command line router client for Zend. Thanks to
 * http://stackoverflow.com/questions/2325338/running-a-zend-framework-action-from-command-line
 *
 * @package    MUtil
 * @subpackage Controller
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since MUtil 1.2
 */
class MUtil_Controller_Router_Cli extends \Zend_Controller_Router_Abstract
{
    /**
     *
     * @param mixed $object
     * @return string
     */
    protected function _expandMessage($object)
    {
        $string = str_replace(
                " [ options ]\n",
                " [ options ] controller [action [name=value [...]]]\n",
                $object->getUsageMessage());

        return $string . "

controller (string)  The name-of-the-controllor with dashes not CamelCase.
action (string)      The name-of-the-action with dashes not CamelCase,
                     the action defaults to 'index' if not specified.
name=value (string)  Zero or more name=value parameter pairs, without the
                     = sign the value defaults to an empty string.\n";
    }

    /**
     * Generates a URL path that can be used in URL creation, redirection, etc.
     *
     * May be passed user params to override ones from URI, Request or even defaults.
     * If passed parameter has a value of null, it's URL variable will be reset to
     * default.
     *
     * If null is passed as a route name assemble will use the current Route or 'default'
     * if current is not yet set.
     *
     * Reset is used to signal that all parameters should be reset to it's defaults.
     * Ignoring all URL specified values. User specified params still get precedence.
     *
     * Encode tells to url encode resulting path parts.
     *
     * @param  array $userParams Options passed by a user used to override parameters
     * @param  mixed $name The name of a Route to use
     * @param  bool $reset Whether to reset to the route defaults ignoring URL params
     * @param  bool $encode Tells to encode URL parts on output
     * @throws \Zend_Controller_Router_Exception
     * @return string Resulting URL path
     */
    public function assemble($userParams, $name = null, $reset = false, $encode = true)
    {
        $url = '';
        foreach ($userParams as $key => $value) {
            // E.g. \Exceptions are stored as parameters
            // and posted arrays can be a problem as well
            if (is_scalar($value)) {
                $url .= '/' . rawurlencode($key) . '/' . rawurlencode($value);
            }
        }
        return $url;
    }

    /**
     * Processes a request and sets its controller and action.  If
     * no route was possible, an exception is thrown.
     *
     * @param  \Zend_Controller_Request_Abstract
     * @throws \Zend_Controller_Router_Exception
     * @return \Zend_Controller_Request_Abstract|boolean
     */
    public function route(\Zend_Controller_Request_Abstract $request)
    {
        $options = array(
            'help|h'   => 'Show this help',
            'org|o=i'  => 'The user organization number',
            'pwd|p=s'  => 'User password',
            'user|u=s' => 'The user name',
        );

        $getopt = new \Zend_Console_Getopt($options);

        try {
            $getopt->parse();
        } catch (\Zend_Console_Getopt_Exception $e) {
            echo $this->_expandMessage($e);
            exit;
        }

        if ($getopt->getOption('h')) {
            // $getopt->s
            echo $this->_expandMessage($getopt);
            exit;
        }

        if ($request instanceof \MUtil_Controller_Request_Cli) {
            $request->setUserLogin(
                    $getopt->getOption('u'),
                    $getopt->getOption('o'),
                    $getopt->getOption('p')
                    );
        }

        $arguments = $getopt->getRemainingArgs();
        if ($arguments)
        {
            $controller = array_shift($arguments);
            $action     = array_shift($arguments);

            if (! $action) {
                $action = 'index';
            }
            if (preg_match('/^\w+(-\w+)*$/', $controller) && preg_match('/^\w+(-\w+)*$/', $action))
            {
                $request->setControllerName($controller);
                $request->setActionName($action);

                $params[$request->getControllerKey()] = $controller;
                $params[$request->getActionKey()]     = $action;

                foreach ($arguments as $arg) {
                    if (\MUtil_String::contains($arg, '=')) {
                        list($name, $value) = explode('=', $arg, 2);
                    } else {
                        $name  = $arg;
                        $value = '';
                    }
                    $params[$name] = $value;
                }

                $request->setParams($params);
                return $request;
            }

            echo "Invalid command: $controller/$action.\n", exit;

        }

        echo "No command given.\n\n";

        echo $this->_expandMessage($getopt), exit;
    }
}
