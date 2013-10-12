<?php
/**
 * This file is part of the Gesdon package.
 *
 * (c) Simon Leblanc <contact@leblanc-simon.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gesdon\Extension\Twig;

use Gesdon\Core\Config as CoreConfig;


/**
 * Config extension for twig class
 *
 * @package     Gesdon\Extension\Twig
 * @version     1.0.0
 * @license     http://opensource.org/licenses/MIT  MIT
 * @author      Simon Leblanc <contact@leblanc-simon.eu>
 */
class Config extends \Twig_Extension
{
    /**
     * Define twig functions
     *
     * @return  array   the array with the functions define
     * @access  public
     */
    public function getFunctions()
    {
        return array(
            'get_config' => new \Twig_Function_Method($this, 'getConfigFunction'),
        );
    }
    
    
    /**
     * Get the class name
     *
     * @return  string  the class name
     * @access  public
     */
    public function getName()
    {
        return __CLASS__;
    }
    
    
    /**
     * Get the value of configuration
     *
     * @param   string  $v  the name of the configuration to retrieve
     * @return  string      the value of the configuration
     * @access  public
     */
    public function getConfigFunction($v)
    {
        return CoreConfig::get($v);
    }
}