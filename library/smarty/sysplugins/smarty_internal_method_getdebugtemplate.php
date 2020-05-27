<?php

/**
 * Smarty Method GetDebugTemplate
 *
 * Smarty::getDebugTemplate() method
 *
 * @package    Smarty
 * @subpackage PluginsInternal
 * @author     Uwe Tews
 */
class Smarty_Internal_Method_GetDebugTemplate
{

    /**
     * Valid for Smarty and template object
     *
     * @var int
     */
    public $objMap = 3;

    /**
     * return name of debugging template
     *
     * @param \Smarty_Internal_TemplateBase|\Smarty_Internal_Template|\Smarty $obj
     *
     * @return string
     * @api Smarty::getDebugTemplate()
     *
     */
    public function getDebugTemplate(Smarty_Internal_TemplateBase $obj)
    {
        $smarty = $obj->_getSmartyObj();
        return $smarty->debug_tpl;
    }
}
