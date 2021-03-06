<?php
/**
 *
 * @package    MUtil
 * @subpackage Batch
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 * The Batch package is for the sequential processing of commands which may
 * take to long to execute in a single request.
 *
 * The abstract batch handles the command stack, keeping track of batch specific
 * counters and messages and the communication to the end user including display
 * of any messages set during execution and reporting back execution errors
 * occured during a run, e.g. when a job throws an exception during execution.
 *
 * The prefereed method to use this object is to write multiple small jobs using
 * \MUtil_Task_TaskInterface and then use \MUtil_Task_TaskBatch to execute these
 * commands.
 *
 * Global objects in the Task will be loaded automatically when they implement the
 * \MUtil_Registry_TargetInterface (the same as happens for with this object). All
 * other parameters for the task should be scalar.
 *
 * The other option use this package by creating a sub class of this class and write
 * the methods that run the code to be executed (and then write the code that adds
 * those functions to be executed). The functions need to return a true value
 * when they completed successfully, otherwise they will be repeated until they do.
 *
 * Each step in the sequence consists of a method name of the child object
 * and any number of scalar variables and array's containing scalar variables.
 *
 * See \MUtil_Batch_WaitBatch for example usage.
 *
 * The storage engine used for the commands is separated from this object so we
 * could use e.g. \Zend_Queue as an alternative for storing the command stack.
 * Currently \MUtil_Batch_Stack_SessionStack is used by default.
 * \MUtil_Batch_Stack_CachStack can be used as well and has the advantage of
 * storing all data in a separate file.
 *
 * @see \MUtil_Task_TaskBatch
 * @see \MUtil_Batch_Stack_StackInterface
 * @see \MUtil_Registry_TargetInterface
 * @see \MUtil_Batch_WaitBatch
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.2
 */
abstract class MUtil_Batch_BatchAbstract extends \MUtil_Registry_TargetAbstract implements \Countable
{
    /**
     * Constant for using console method = run batch in one long run from the console
     */
    const CONS = 'Cons';

    /**
     * Constant for using push method = run batch by short separate ajax calls from the browser
     */
    const PULL = 'Pull';

    /**
     * Constant for using push method = run batch one long run in an iframe receiving javescript commands
     */
    const PUSH = 'Push';

    protected $_buttonClass = 'btn';

    /**
     *
     * @var float The timer for _checkReport()
     */
    private $_checkReportStart = null;

    /**
     * Optional form id, for using extra params from the form during batch execution.
     *
     * @var string
     */
    protected $_formId;

    /**
     * Name to prefix the functions, to avoid naming clashes.
     *
     * @var string Default is the classname with an extra underscore
     */
    protected $_functionPrefix;

    /**
     * An id unique for this session.
     *
     * @var string Unique id
     */
    private $_id;
    
    /**
     * Stack to keep existing id's.
     *
     * @var array
     */
    private static $_idStack = array();

    /**
     * Holds the last message set by the batch job
     *
     * @var string
     */
    private $_lastMessage = null;

    /**
     *
     * @var array of callables for logging addMessage messages
     */
    private $_loggers = array();

    /**
     *
     * @var string
     */
    private $_messageLogFile;

    /**
     *
     * @var boolean
     */
    private $_messageLogWhenAdding = false;

    /**
     *
     * @var boolean
     */
    private $_messageLogWhenSetting = false;

    /**
     * Progress template
     * 
     * Available placeholders:
     * {total}      Total time
     * {elapsed}    Elapsed time
     * {remaining}  Remaining time
     * {percent}    Progress percent without the % sign
     * {msg}        Message reveiced
     * 
     * @var string 
     */
    private $_progressTemplate = "{percent}% {msg}";

    /**
     * Session for storage of output results
     *
     * @var \Zend_Session_Namespace
     */
    private $_session;

    /**
     * When true the progressbar should start immediately. When false the user has to perform an action.
     *
     * @var boolean
     */
    public $autoStart = false;

    /**
     * The number of bytes to pad during push communication in Kilobytes.
     *
     * This is needed as many servers need extra output passing to avoid buffering.
     *
     * Also this allows you to keep the server buffer high while using this JsPush.
     *
     * @var int
     */
    public $extraPushPaddingKb = 0;

    /**
     * Manual set of the finish url. Setting an empty string will disable the finish redirect
     * @var string
     */
    public $finishUrl;

    /**
     * The number of bytes to pad for the first push communication in Kilobytes. If zero
     * $extraPushPaddingKb is used.
     *
     * This is needed as many servers need extra output passing to avoid buffering.
     *
     * Also this allows you to keep the server buffer high while using this JsPush.
     *
     * @var int
     */
    public $initialPushPaddingKb = 0;

    /**
     *
     * @var \Zend_Log
     */
    protected $log;

    /**
     * The mode to use for the panel: PUSH or PULL
     *
     * @var string
     */
    protected $method = self::PULL;

    /**
     * Date format for logging messages
     *
     * @var string
     */
    protected $messageLogDateFormat = 'Y-m-d H:i:s';

    /**
     * Message log format string for date string and message string
     *
     * @var string
     */
    protected $messageLogFormatString = '[%s]: %s';

    /**
     * The minimal time used between send progress reports.
     *
     * This enables quicker processing as multiple steps can be taken in a single
     * run(), without the run taking too long to answer.
     *
     * Set to 0 to report back on each step.
     *
     * @var int
     */
    public $minimalStepDurationMs = 1000;

    /**
     *
     * @var \Zend_ProgressBar
     */
    protected $progressBar;

    /**
     *
     * @var \Zend_ProgressBar_Adapter
     */
    protected $progressBarAdapter;

    /**
     * The name of the parameter used for progress panel signals
     *
     * @var string
     */
    public $progressParameterName = 'progress';

    /**
     * The value required for the progress panel to report and reset
     *
     * @var string
     */
    public $progressParameterReportValue = 'report';

    /**
     * The value required for the progress panel to restart
     *
     * The value isn't used in itself, but having an empty value screws up the url
     * interpretation.
     *
     * @var string
     */
    public $progressParameterRestartValue = 'restart';

    /**
     * The value required for the progress panel to start running
     *
     * @var string
     */
    public $progressParameterRunValue = 'run';

    /**
     * The command stack
     *
     * @var \MUtil_Batch_Stack_Stackinterface
     */
    protected $stack;

    /**
     * The place to store new variables for the source
     *
     * @var \ArrayObject
     */
    protected $variables;

    /**
     *
     * @param string $id A unique name identifying this batch
     * @param \MUtil_Batch_Stack_Stackinterface $stack Optional different stack than session stack
     */
    public function __construct($id, \MUtil_Batch_Stack_Stackinterface $stack = null)
    {
        $id = preg_replace('/[^a-zA-Z0-9_]/', '', $id);

        if (isset(self::$_idStack[$id])) {
            throw new \MUtil_Batch_BatchException("Duplicate batch id created: '$id'");
        }
        self::$_idStack[$id] = $id;

        $this->_id = $id;

        if (null === $stack) {
            $stack = new \MUtil_Batch_Stack_SessionStack($id);
        }
        $this->stack = $stack;

        $this->_initSession($id);

        if (\MUtil_Console::isConsole()) {
            $this->method = self::CONS;
        }
    }

    /**
     * Check if the aplication should report back to the user
     *
     * @return boolean True when application should report to the user
     */
    private function _checkReport()
    {
        // @TODO Might be confusing if one of the first steps adds more steps, make this optional?
        if (1 === $this->_session->processed) {
            return true;
        }

        if (null === $this->_checkReportStart) {
            $this->_checkReportStart = microtime(true) + ($this->minimalStepDurationMs / 1000);
            return false;
        }

        if (microtime(true) > $this->_checkReportStart) {
            $this->_checkReportStart = null;
            return true;
        }

        return false;
    }

    /**
     * Signal an loop item has to be run again.
     */
    protected function _extraRun()
    {
        $this->_session->count = $this->_session->count + 1;
        $this->_session->processed = $this->_session->processed + 1;
    }

    /**
     * Helper function to complete the progressbar.
     */
    protected function _finishBar()
    {
        $this->_session->finished  = true;

        $bar = $this->getProgressBar();
        $bar->finish();
    }

    /**
     * Initialize persistent storage
     *
     * @param string $name The id of this batch
     */
    private function _initSession($id)
    {
        $this->_session = new \Zend_Session_Namespace(get_class($this) . '_' . $id);

        if (! isset($this->_session->processed)) {
            $this->reset();
        }
    }

    /**
     * Helper function to update the progressbar.
     */
    protected function _updateBar()
    {
        $this->getProgressBar()->update($this->getProgressPercentage(), $this->getLastMessage());
    }
    
    /**
     * Add to exception store
     * @param \Exception $e
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function addException(\Exception $e)
    {
        $message = $e->getMessage();

        $this->addMessage($message);
        $this->_session->exceptions[] = $message;

        if ($this->log instanceof \Zend_Log) {
            $messages[] = $message;

            $previous = $e->getPrevious();
            while ($previous) {
                $messages[] = '  Previous exception: ' . $previous->getMessage();
                $previous = $previous->getPrevious();
            }
            $messages[] = $e->getTraceAsString();

            $this->log->log(implode("\n", $messages), \Zend_Log::ERR);
        }
        return $this;
    }

    /**
     *
     * @param callable $function Function to call with text message
     * @return $this
     */
    public function addLogFunction($function)
    {
        $this->_loggers[] = $function;

        return $this;
    }

    /**
     * Add an execution step to the command stack.
     *
     * @param string $method Name of a method of this object
     * @param mixed $param1 Optional scalar or array with scalars, as many parameters as needed allowed
     * @param mixed $param2 ...
     * @return \MUtil_Task_TaskBatch (continuation pattern)
     */
    protected function addStep($method, $param1 = null)
    {
        if (! method_exists($this, $method)) {
            throw new \MUtil_Batch_BatchException("Invalid batch method: '$method'.");
        }

        $params = array_slice(func_get_args(), 1);

        if ($this->stack->addStep($method, $params)) {
            $this->_session->count = $this->_session->count + 1;
        }

        return $this;
    }
    
    /**
     * Allow to add steps to the counter
     * 
     * This should only be used by iterable tasks that execute in more then 1 step
     * 
     * @param int $number
     */
    public function addStepCount($number)
    {
        if ($number > 0) {
            $this->_session->count = $this->_session->count + $number;
        }
    }    

    /**
     * Add a message to the message stack.
     *
     * @param string $text A message to the user
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function addMessage($text)
    {
        $this->_session->messages[] = $text;
        $this->_lastMessage = $text;

        if ($this->_messageLogWhenAdding && $this->_messageLogFile) {
            $this->logMessage($text);
        }

        foreach ($this->_loggers as $function) {
            call_user_func($function, $text);
        }

        return $this;
    }

    /**
     * Increment a named counter
     *
     * @param string $name
     * @param integer $add
     * @return integer
     */
    public function addToCounter($name, $add = 1)
    {
        if (! isset($this->_session->counters[$name])) {
            $this->_session->counters[$name] = 0;
        }
        $this->_session->counters[$name] += $add;

        return $this->_session->counters[$name];
    }

    /**
     * The number of commands in this batch (both processed
     * and unprocessed).
     *
     * @return int
     */
    public function count()
    {
        return $this->_session->count;
    }

    /**
     * Return the value of a named counter
     *
     * @param string $name
     * @return integer
     */
    public function getCounter($name)
    {
        if (isset($this->_session->counters[$name])) {
            return $this->_session->counters[$name];
        }

        return 0;
    }

    /**
     * Return the stored exceptions.
     *
     * @return array of \Exceptions
     */
    public function getExceptions()
    {
        return $this->_session->exceptions;
    }

    /**
     * Get the optional form id, for using extra params from the form during batch execution.
     *
     * @return string
     */
    public function getFormId()
    {
        return $this->_formId;
    }

    /**
     * Returns the prefix used for the function names for the PUSH method to avoid naming clashes.
     *
     * Set automatically to get_class($this) . '_' $this->_id . '_', use different name
     * in case of name clashes.
     *
     * @see setFunctionPrefix()
     *
     * @return string
     */
    protected function getFunctionPrefix()
    {
        if (! $this->_functionPrefix) {
            $this->setFunctionPrefix(get_class($this) . '_' . $this->_id . '_');
        }

        return (string) $this->_functionPrefix;
    }

    /**
     * Return the batch id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Returns the lat message set for feedback to the user.
     * @return string
     */
    public function getLastMessage()
    {
        return $this->_lastMessage;
    }

    /**
     * Get a message from the message stack with a specific id.
     *
     * @param scalar $id
     * @param string $default A default message
     * @return string
     */
    public function getMessage($id, $default = null)
    {
        if (array_key_exists($id, $this->_session->messages)) {
            return $this->_session->messages[$id];
        } else {
            return $default;
        }
    }

    /**
     * String of messages from the batch
     *
     * Do not forget to reset() the batch if you're done with it after
     * displaying the report.
     *
     * @param boolean $reset When true the batch is reset afterwards
     * @return array
     */
    public function getMessages($reset = false)
    {
        $messages = $this->_session->messages;

        if ($reset) {
            $this->reset();
        }

        return $messages;
    }

    /**
     * Return a progress panel object, set up to be used by
     * this batch.
     *
     * @param \Zend_View_Abstract $view
     * @param mixed $arg_array \MUtil_Ra::args() arguments to populate progress bar with
     * @return \MUtil_Html_ProgressPanel
     */
    public function getPanel(\Zend_View_Abstract $view, $arg_array = null)
    {
        $args = func_get_args();

        \MUtil_JQuery::enableView($view);
        //$jquery = $view->jQuery();
        //$jquery->enable();

        if (isset($this->finishUrl)) {
            $urlFinish = $this->finishUrl;
        } else {
            $urlFinish = $view->url(array($this->progressParameterName => $this->progressParameterReportValue));
        }
        $urlRun    = $view->url(array($this->progressParameterName => $this->progressParameterRunValue));

        $panel = new \MUtil_Html_ProgressPanel($args);
        $panel->id = $this->_id;

        $js = new \MUtil_Html_Code_JavaScript(dirname(__FILE__) . '/Batch' . $this->method . '.js');
        $js->setInHeader(false);
        // Set the fields, in case they where not set earlier
        $js->setDefault('__AUTOSTART__', $this->autoStart ? 'true' : 'false');
        $js->setDefault('{PANEL_ID}', '#' . $this->_id);
        $js->setDefault('{FORM_ID}', $this->_formId);
        $js->setDefault('{TEMPLATE}', $this->_progressTemplate);
        $js->setDefault('{TEXT_ID}', $panel->getDefaultChildTag() . '.' . $panel->progressTextClass);
        $js->setDefault('{URL_FINISH}', addcslashes($urlFinish, "/"));
        $js->setDefault('{URL_START_RUN}', addcslashes($urlRun, "/"));
        $js->setDefault('FUNCTION_PREFIX_', $this->getFunctionPrefix());

        $panel->append($js);

        return $panel;
    }

    /**
     * The Zend ProgressBar handles the communication through
     * an adapter interface.
     *
     * @return \Zend_ProgressBar
     */
    public function getProgressBar()
    {
        if (! $this->progressBar instanceof \Zend_ProgressBar) {
            $this->setProgressBar(
                    new \Zend_ProgressBar($this->getProgressBarAdapter(), 0, 100, $this->_session->getNamespace() . '_pb')
                    );
        }
        return $this->progressBar;
    }

    /**
     * The communication adapter for the ProgressBar.
     *
     * @return \Zend_ProgressBar_Adapter
     */
    public function getProgressBarAdapter()
    {
        // Create the current adapter when it does not exist or does not accord with the method.
        switch ($this->method) {
            case self::CONS:
                if (! $this->progressBarAdapter instanceof \Zend_ProgressBar_Adapter_Console) {
                    $this->setProgressBarAdapter(new \Zend_ProgressBar_Adapter_Console());
                }
                break;

            case self::PULL:
                if (! $this->progressBarAdapter instanceof \Zend_ProgressBar_Adapter_JsPull) {
                    $this->setProgressBarAdapter(new \Zend_ProgressBar_Adapter_JsPull());
                }
                break;

            default:
                if (! $this->progressBarAdapter instanceof \Zend_ProgressBar_Adapter_JsPush) {
                    $this->setProgressBarAdapter(new \MUtil_ProgressBar_Adapter_JsPush());
                }
        }

        // Check for extra padding
        if ($this->progressBarAdapter instanceof \MUtil_ProgressBar_Adapter_JsPush) {
            $this->progressBarAdapter->initialPaddingKb = $this->initialPushPaddingKb;
            $this->progressBarAdapter->extraPaddingKb   = $this->extraPushPaddingKb;
        }

        return $this->progressBarAdapter;
    }

    /**
     * Get the current progress percentage
     *
     * @return float
     */
    public function getProgressPercentage()
    {
        return round($this->_session->processed / max($this->_session->count, 1) * 100, 2);
    }

    /**
     * Returns a button that can be clicked to restart the progress bar.
     *
     * @param mixed $arg_array \MUtil_Ra::args() arguments to populate link with
     * @return \MUtil_Html_HtmlElement
     */
    public function getRestartButton($args_array = 'Restart')
    {
        $args = \MUtil_Ra::args(func_get_args());
        $args['onclick'] = new \MUtil_Html_OnClickArrayAttribute(
            new \MUtil_Html_Raw('if (! this.disabled) {location.href = "'),
            new \MUtil_Html_HrefArrayAttribute(
                    array($this->progressParameterName => $this->progressParameterRestartValue)
                    ),
            new \MUtil_Html_Raw('";} this.disabled = true; event.cancelBubble=true;'));

        $button = new \MUtil_Html_HtmlElement('button', $args);
        $button->appendAttrib('class', $this->_buttonClass.' btn-succes');

        return $button;
    }

    /**
     * Returns a link that can be clicked to restart the progress bar.
     *
     * @param mixed $arg_array \MUtil_Ra::args() arguments to populate link with
     * @return \MUtil_Html_AElement
     */
    public function getRestartLink($args_array = 'Restart')
    {
        $args = \MUtil_Ra::args(func_get_args());
        $args['href'] = array($this->progressParameterName => $this->progressParameterRestartValue);

        return new \MUtil_Html_AElement($args);
    }

    /**
     * Return a variable from the session store.
     *
     * @param string $name Name of the variable
     * @return mixed (continuation pattern)
     */
    public function getSessionVariable($name)
    {
        if (isset($this->_session->source, $this->_session->source[$name])) {
            return $this->_session->source[$name];
        }
        return null;
    }

    /**
     * Return the variables from the session store.
     *
     * @return \ArrayObject or null
     */
    protected function getSessionVariables()
    {
        if (isset($this->_session->source)) {
            return $this->_session->source;
        }
        return null;
    }

    /**
     * Get the current stack
     *
     * @return \MUtil_Batch_Stack_Stackinterface
     */
    public function getStack()
    {
        return $this->stack;
    }

    /**
     * Returns a button that can be clicked to start the progress bar.
     *
     * @param mixed $arg_array \MUtil_Ra::args() arguments to populate link with
     * @return \MUtil_Html_HtmlElement
     */
    public function getStartButton($args_array = 'Start')
    {
        $args = \MUtil_Ra::args(func_get_args());
        $args['onclick'] = 'if (! this.disabled) {' . $this->getFunctionPrefix() .
                'Start();} this.disabled = true; event.cancelBubble=true;';

        $button = new \MUtil_Html_HtmlElement('button', $args);
        $button->appendAttrib('class', $this->_buttonClass.' btn-succes');

        return $button;
    }

    /**
     * Return a variable from the general store or from the session store if it exist there.
     *
     * @param string $name Name of the variable
     * @return mixed (continuation pattern)
     */
    public function getVariable($name)
    {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }
        return $this->getSessionVariable($name);
    }

    /**
     * Return whether a session variable exists in the session store.
     *
     * @param string $name Name of the variable
     * @return boolean
     */
    public function hasSessionVariable($name)
    {
        return isset($this->_session->source, $this->_session->source[$name]);
    }

    /**
     * Return whether a variable exists the general store or in the session store.
     *
     * @param string $name Name of the variable
     * @return boolean
     */
    public function hasVariable($name)
    {
        return isset($this->variables[$name]) || $this->hasSessionVariable($name);
    }

    /**
     * Return true if running in console mode.
     *
     * @return boolean
     */
    public function isConsole()
    {
        return self::CONS === $this->method;
    }

    /**
     * Return true after commands all have been ran.
     *
     * @return boolean
     */
    public function isFinished()
    {
        return $this->_session->finished;
    }

    /**
     * Return true when at least one command has been loaded.
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->_session->count || $this->_session->processed;
    }

    /**
     * Does the batch use the PULL method for communication.
     *
     * @return boolean
     */
    public function isPull()
    {
        return $this->method === self::PULL;
    }

    /**
     * Does the batch use the PUSH method for communication.
     *
     * @return boolean
     */
    public function isPush()
    {
        return $this->method === self::PUSH;
    }

    /**
     *
     * @param string $text Line to log
     * @return $this
     */
    public function logMessage($text = null)
    {
        if ($this->_messageLogFile) {
            if ($text) {
                $text = sprintf($this->messageLogFormatString, gmdate($this->messageLogDateFormat), $text);
            }
            file_put_contents($this->_messageLogFile, $text . PHP_EOL, FILE_APPEND);
        }

        return $this;
    }

    /**
     * Reset and empty the session storage
     *
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function reset()
    {
        $this->_session->unsetAll();

        $this->_session->count      = 0;
        $this->_session->counters   = array();
        $this->_session->exceptions = array();
        $this->_session->finished   = false;
        $this->_session->messages   = array();
        $this->_session->processed  = 0;

        $this->stack->reset();

        return $this;
    }

    /**
     * Reset a named counter
     *
     * @param string $name
     * @return $this
     */
    public function resetCounter($name)
    {
        unset($this->_session->counters[$name]);

        return $this;
    }

    /**
     * Reset a message on the message stack with a specific id.
     *
     * @param scalar $id
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function resetMessage($id)
    {
        unset($this->_session->messages[$id]);

        return $this;
    }

    /**
     * Run as much code as possible, but do report back.
     *
     * Returns true if any output was communicated, i.e. the "normal"
     * page should not be displayed.
     *
     * @param \Zend_Controller_Request_Abstract $request
     * @return boolean True when something ran
     */
    public function run(\Zend_Controller_Request_Abstract $request)
    {
        // Check for run url
        if ($request->getParam($this->progressParameterName) === $this->progressParameterRunValue) {
            // [Try to] remove the maxumum execution time for this session
            @ini_set("max_execution_time", 0);
            @set_time_limit(0);

            if ($this->isPush()) {
                return $this->runContinuous();
            }

            // Is there something to run?
            if ($this->isFinished() || (! $this->isLoaded())) {
                return false;
            }

            while ($this->step()) {
                // error_log('Cur: ' . microtime(true) . ' report is '. (microtime(true) > $reportRun ? 'true' : 'false'));
                if ($this->_checkReport()) {
                    // Communicate progress
                    $this->_updateBar();
                    return true;
                }
            }

            // Only reached when at end of commands
            $this->_finishBar();

            // There is progressBar output
            return true;
        } else {
            // No ProgressBar output
            return false;
        }
    }

    /**
     * Run the whole batch at once, without communicating with a progress bar.
     *
     * @return int Number of steps taken
     */
    public function runAll()
    {
        // [Try to] remove the maxumum execution time for this session
        @ini_set("max_execution_time", 0);
        @set_time_limit(0);

        while ($this->step());

        return $this->_session->processed;
    }

    /**
     * Run the whole batch at once, while still communicating with a progress bar.
     *
     * @return boolean True when something ran
     */
    public function runContinuous()
    {
        // Is there something to run?
        if ($this->isFinished() || (! $this->isLoaded())) {
            return false;
        }

        // [Try to] remove the maxumum execution time for this session
        @ini_set("max_execution_time", 0);
        @set_time_limit(0);

        while ($this->step()) {
            if ($this->_checkReport()) {
                // Communicate progress
                $this->_updateBar();
            }
        }
        $this->_updateBar();
        $this->_finishBar();

        return true;
    }

    /**
     * Set the optional form id, for using extra params from the form during batch execution.
     *
     * @param string $id
     * @return \MUtil_Html_ProgressPanel (continuation pattern)
     */
    public function setFormId($id)
    {
        $this->_formId = $id;
        return $this;
    }

    /**
     * Name prefix for PUSH functions.
     *
     * Set automatically to get_class($this) . '_' $this->_id . '_', use different name
     * in case of name clashes.
     *
     * @param string $prefix
     * @return \MUtil_Html_ProgressPanel (continuation pattern)
     */
    public function setFunctionPrefix($prefix)
    {
        $this->_functionPrefix = $prefix;
        return $this;
    }

    /**
     * Add/set a message on the message stack with a specific id.
     *
     * @param scalar $id
     * @param string $text A message to the user
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function setMessage($id, $text)
    {
        $this->_session->messages[$id] = $text;
        $this->_lastMessage = $text;

        if ($this->_messageLogWhenSetting && $this->_messageLogFile) {
            $this->logMessage($text);
        }

        return $this;
    }

    /**
     *
     * @param string $filename Filename to log to
     * @param boolean $logSet Log setMessage calls
     * @param boolean $logAdd Log addMessage calls
     * @return $this
     */
    public function setMessageLogFile($filename, $logSet = true, $logAdd = true)
    {
        $this->_messageLogFile        = $filename;
        $this->_messageLogWhenSetting = $logSet && $filename;
        $this->_messageLogWhenAdding  = $logAdd && $filename;

        return $this;
    }

    /**
     * Sets the communication method for progress reporting.
     *
     * @param string $method One of the constants of this object
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function setMethod($method)
    {
        switch ($method) {
            case self::PULL:
            case self::PUSH:
                $this->method = $method;
                return $this;

            default:
                throw new \MUtil_Batch_BatchException("Invalid batch usage method '$method'.");
        }
    }

    /**
     * Set the communication method used by this batch to PULL.
     *
     * This is the most stable method as it works independently of
     * server settings. Therefore it is the default method.
     *
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function setMethodPull()
    {
        $this->setMethod(self::PULL);

        return $this;
    }

    /**
     * Set the communication method used by this batch to PUSH.
     *
     * I.e. the start page opens an iFrame, the url of the iFrame calls the
     * batch with the RUN parameter and the process returns JavaScript tags
     * that handle the progress reporting.
     *
     * This is a very fast and resource inexpensive method for batch processing
     * but it is only suitable for short running processes as servers tend to
     * cut off http calls that take more than some fixed period of time to run -
     * even when those processes keep returning data.
     *
     * Another problem with this method is buffering, i.e. the tendency of servers
     * to wait sending data back until a process has been completed or enough data
     * has been send.
     *
     * E.g. on IIS 7 you have to adjust the file %windir%\System32\inetsrv\config\applicationHost.config
     * and add the attribute responseBufferLimit="1024" twice, both to
     * ../handlers/add name="PHP_via_FastCGI" and to ../handlers/add name="CGI-exe".
     *
     * Still the above works only partially, IIS tends to wait longer before sending the
     * first batch of data. The trick is to add extra spaces to the output until the
     * threshold is reached. This is done by specifying the $extraPaddingKb parameter.
     * Just increase it until it works.
     *
     * @param int $extraPaddingKb
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function setMethodPush($extraPaddingKb = null)
    {
        $this->setMethod(self::PUSH);

        if ((null !== $extraPaddingKb) && is_numeric($extraPaddingKb)) {
            $this->extraPushPaddingKb = $extraPaddingKb;
        }

        return $this;
    }

    /**
     * The Zend ProgressBar handles the communication through
     * an adapter interface.
     *
     * @param \Zend_ProgressBar $progressBar
     * @return \MUtil_Html_ProgressPanel (continuation pattern)
     */
    public function setProgressBar(\Zend_ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
        return $this;
    }

    /**
     * The communication adapter for the ProgressBar.
     *
     * @param \Zend_ProgressBar_Adapter_Interface $adapter
     * @return \MUtil_Html_ProgressPanel (continuation pattern)
     */
    public function setProgressBarAdapter(\Zend_ProgressBar_Adapter $adapter)
    {
        if ($adapter instanceof \Zend_ProgressBar_Adapter_JsPush) {
            $prefix = $this->getFunctionPrefix();

            // Set the fields, in case they where not set earlier
            $adapter->setUpdateMethodName($prefix . 'Update');
            $adapter->setFinishMethodName($prefix . 'Finish');
        }

        $this->progressBarAdapter = $adapter;
        return $this;
    }
    
    /**
     * Set the progress template
     * 
     * Available placeholders:
     * {total}      Total time
     * {elapsed}    Elapsed time
     * {remaining}  Remaining time
     * {percent}    Progress percent without the % sign
     * {msg}        Message reveiced
     * 
     * @var string 
     */
    public function setProgressTemplate($template)
    {
        $this->_progressTemplate = $template;
    }

    /**
     * Store a variable in the session store.
     *
     * @param string $name Name of the variable
     * @param mixed $variable Something that can be serialized
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function setSessionVariable($name, $variable)
    {
        if (! isset($this->_session->source)) {
            $this->_session->source = new \ArrayObject();
        }

        $this->_session->source[$name] = $variable;
        return $this;
    }

    /**
     * Add/set an execution step to the command stack. Named to prevent double addition.
     *
     * @param string $method Name of a method of this object
     * @param mixed $id A unique id to prevent double adding of something to do
     * @param mixed $param1 Scalar or array with scalars, as many parameters as needed allowed
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    protected function setStep($method, $id, $param1 = null)
    {
        if (! method_exists($this, $method)) {
            throw new \MUtil_Batch_BatchException("Invalid batch method: '$method'.");
        }

        $params = array_slice(func_get_args(), 2);

        if ($this->stack->setStep($method, $id, $params)) {
            $this->_session->count = $this->_session->count + 1;
        }

        return $this;
    }

    /**
     * Store a variable in the general store.
     *
     * These variables have to be reset for every run of the batch.
     *
     * @param string $name Name of the variable
     * @param mixed $variable Something that can be serialized
     * @return \MUtil_Batch_BatchAbstract (continuation pattern)
     */
    public function setVariable($name, $variable)
    {
        if (null === $this->variables) {
            $this->variables = new \ArrayObject();
        }

        $this->variables[$name] = $variable;
        return $this;
    }

    /**
     * Progress a single step on the command stack
     *
     * @return boolean
     */
    protected function step()
    {
        if ($this->stack->hasNext()) {

            try {
                $command = $this->stack->getNext();
                if (! isset($command[0], $command[1])) {
                    throw new \MUtil_Batch_BatchException("Invalid batch command: '$command'.");
                }
                list($method, $params) = $command;

                if (! method_exists($this, $method)) {
                    throw new \MUtil_Batch_BatchException("Invalid batch method: '$method'.");
                }

                if (call_user_func_array(array($this, $method), $params)) {
                    $this->stack->gotoNext();
                }
                $this->_session->processed = $this->_session->processed + 1;

            } catch (\Exception $e) {
                $this->addMessage('ERROR!!!');
                $this->addMessage(
                        'While calling:' . $command[0] . '(' . implode(',', \MUtil_Ra::flatten($command[1])) . ')'
                        );
                $this->addException($e);
                $this->stopBatch($e->getMessage());

                //\MUtil_Echo::track($e);
            }
            return true;
        } else {
            return false;
        }
    }

    public function stopBatch($message)
    {
        // Set to stopped
        $this->_session->finished = true;

        // Cleanup stack
        $this->stack->reset();

        $this->addMessage($message);
    }
    
    /**
     * Unload a batch
     * 
     * Normally we don't need this, but in unit test we need to be able to run a batch after is was finished
     * 
     * @param string $id
     */
    public static function unload($id) {
        unset(self::$_idStack[$id]);
    }
}
