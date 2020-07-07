<?php

/**
 *
 * @package    MUtil
 * @subpackage Markup
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

/**
 * Extends \Zend_Markup with extra utility functions
 *
 * @package    MUtil
 * @subpackage Markup
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Markup extends \Zend_Markup
{
    /**
     * Factory pattern
     *
     * @param  string $parser
     * @param  string $renderer
     * @param  array $options
     * @return \Zend_Markup_Renderer_RendererAbstract
     */
    public static function factory($parser, $renderer = 'Html', array $options = array())
    {
        $parserClass   = self::getParserLoader()->load($parser);
        $rendererClass = self::getRendererLoader()->load($renderer);

        $parser            = new $parserClass();
        $options['parser'] = $parser;
        $rendererObject    = new $rendererClass($options);

        if ('Html' == $renderer) {
            $rendererObject->addMarkup(
                'email',
                \Zend_Markup_Renderer_RendererAbstract::TYPE_CALLBACK,
                array(
                    'callback' => new \MUtil_Markup_Renderer_Html_Email(),
                    'group' => 'inline',)
            );
            $rendererObject->addMarkup(
                'url',
                \Zend_Markup_Renderer_RendererAbstract::TYPE_CALLBACK,
                array(
                    'callback' => new \MUtil_Markup_Renderer_Html_Url(),
                    'group' => 'inline',)
            );
        } else {
            $rendererObject->addMarkup(
                'email',
                \Zend_Markup_Renderer_RendererAbstract::TYPE_CALLBACK,
                array(
                    'callback' => new \MUtil_Markup_Renderer_Text_Email(),
                    'group' => 'inline',)
            );
            $rendererObject->addMarkup(
                'url',
                \Zend_Markup_Renderer_RendererAbstract::TYPE_CALLBACK,
                array(
                    'callback' => new \MUtil_Markup_Renderer_Text_Url(),
                    'group' => 'inline',)
            );
        }

        return $rendererObject;
    }

    /**
     * Get the renderer loader
     *
     * @return \Zend_Loader_PluginLoader
     */
    public static function getRendererLoader()
    {
        if (!(self::$_rendererLoader instanceof \Zend_Loader_PluginLoader)) {
            self::$_rendererLoader = new \Zend_Loader_PluginLoader(array(
                'Zend_Markup_Renderer'  => 'Zend/Markup/Renderer/',
                'MUtil_Markup_Renderer' => 'MUtil/Markup/Renderer/',
            ));
        }

        return self::$_rendererLoader;
    }

    /**
     * Check if the URI is valid
     *
     * @param string $uri
     *
     * @return bool
     */
    public static function isValidUri($uri)
    {
        if (!preg_match('/^([a-z][a-z+\-.]*):/i', $uri, $matches)) {
            return '/' == $uri[0];
        }

        $scheme = strtolower($matches[1]);

        switch ($scheme) {
            case 'javascript':
                // JavaScript scheme is not allowed for security reason
                return false;

            case 'http':
            case 'https':
            case 'ftp':
                $components = @parse_url($uri);

                if ($components === false) {
                    return false;
                }

                if (!isset($components['host'])) {
                    return false;
                }

                return true;

            default:
                return true;
        }
    }

    public static function render($content, $parser, $renderer = 'Html', array $options = array())
    {
        $renderer = \MUtil_Markup::factory($parser, $renderer, $options);
        return $renderer->render($content ? $content : '');
    }
}
